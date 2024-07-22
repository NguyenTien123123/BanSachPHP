<?php
session_start();
include 'db_connect.php'; // Kết nối tới CSDL

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Lấy danh sách sách sắp hoặc đã hết hàng
$sql = "SELECT TenSach, SoLuong FROM sach WHERE SoLuong <= 5";
$result = $conn->query($sql);
$low_stock_books = [];
while ($row = $result->fetch_assoc()) {
    $low_stock_books[] = $row;
}

// Thiết lập giá trị mặc định cho khoảng thời gian thống kê
$start_date = date('Y-m-d 00:00:00');
$end_date = date('Y-m-d 23:59:59');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_range = explode(' - ', $_POST['date_range']);
    $start_date = DateTime::createFromFormat('d/m/Y', trim($date_range[0]))->format('Y-m-d 00:00:00');
    $end_date = DateTime::createFromFormat('d/m/Y', trim($date_range[1]))->format('Y-m-d 23:59:59');
}

// Lấy dữ liệu doanh thu theo khoảng thời gian đã chọn
$revenue_query = "
    SELECT DATE(NgayDatHang) as period, SUM(TongTien) AS revenue 
    FROM donhang 
    WHERE TrangThai = 'Completed' AND NgayDatHang BETWEEN '$start_date' AND '$end_date'
    GROUP BY period
    ORDER BY period
";
$revenue_result = $conn->query($revenue_query);

$revenue_data = [];
while ($row = $revenue_result->fetch_assoc()) {
    $revenue_data[$row['period']] = $row['revenue'];
}

// Tạo các mốc thời gian với giá trị doanh thu mặc định là 0
$periods = [];
$revenues = [];
$current_date = new DateTime($start_date);
$end_date_time = new DateTime($end_date);
while ($current_date <= $end_date_time) {
    $period = $current_date->format('Y-m-d');
    $periods[] = $period;
    $revenues[] = $revenue_data[$period] ?? 0;
    $current_date->modify('+1 day');
}

// Đếm số lượng đơn hàng
$sql = "SELECT COUNT(*) AS count FROM donhang WHERE NgayDatHang BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($sql);
$count_orders = $result->fetch_assoc()['count'];

// Đếm số lượng đơn hàng theo trạng thái
$sql = "SELECT COUNT(*) AS count FROM donhang WHERE TrangThai = 'Completed' AND NgayDatHang BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($sql);
$count_completed = $result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) AS count FROM donhang WHERE TrangThai = 'Pending' AND NgayDatHang BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($sql);
$count_pending = $result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) AS count FROM donhang WHERE TrangThai = 'Processing' AND NgayDatHang BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($sql);
$count_processing = $result->fetch_assoc()['count'];  // Đổi từ $count_completed thành $count_processing

$sql = "SELECT COUNT(*) AS count FROM donhang WHERE TrangThai = 'Cancelled' AND NgayDatHang BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($sql);
$count_cancelled = $result->fetch_assoc()['count'];

// Tính tổng doanh thu đã nhận và ước tính
$sql = "SELECT SUM(TongTien) AS revenue FROM donhang WHERE TrangThai = 'Completed' AND NgayDatHang BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($sql);
$revenue_received = $result->fetch_assoc()['revenue'];

$sql = "SELECT SUM(TongTien) AS revenue FROM donhang WHERE TrangThai IN ('Completed', 'Pending') AND NgayDatHang BETWEEN '$start_date' AND '$end_date'";
$result = $conn->query($sql);
$revenue_estimated = $result->fetch_assoc()['revenue'];

$order_data = [
    'Total' => $count_orders,
    'Completed' => $count_completed,
    'Pending' => $count_pending,
    'Processing' => $count_processing,
    'Cancelled' => $count_cancelled,
];

$periods_json = json_encode($periods); // Đảm bảo mảng chứa chuỗi
$revenues_json = json_encode($revenues, JSON_NUMERIC_CHECK); // Đảm bảo số được mã hóa đúng
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        .sidebar {
            background-color: #2e8b57;
            color: #ecf0f1;
            padding: 20px;
            height: 100vh;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
        }

        .sidebar h1 {
            text-align: center;
            color: #3cb371;
            margin-bottom: 30px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a {
            color: black;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .sidebar ul li a:hover {
            background-color: #3cb371;
            color: #fff;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 70px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            color: #3cb371;
        }

        .stat-box {
            background-color: #3cb371;
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-box h3 {
            margin: 0;
            font-size: 1.5em;
        }

        .stat-box ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .stat-box ul li {
            margin: 10px 0;
        }

        .time-range-form {
            text-align: center;
            margin-bottom: 20px;
        }

        .time-range-form select {
            padding: 5px;
            margin: 0 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .time-range-form button {
            padding: 5px 10px;
            background-color: #3cb371;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .time-range-form button:hover {
            background-color: #2e8b57;
        }

        .info-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }

        .info-box {
            flex: 1 1 calc(50% - 20px);
            height: auto;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .info-box.left {
            background-color: #fff;
        }

        .info-box.right {
            background-color: #fff;
        }

        .info-box h4 {
            color: #2e8b57;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                height: auto;
                width: 100%;
                position: relative;
            }

            .content {
                margin-left: 0;
                padding-top: 20px;
            }

            .info-container {
                flex-direction: column;
                align-items: center;
            }

            .info-box {
                flex: 1 1 100%;
                margin: 10px 0;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <h1>Admin Dashboard</h1>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_Report.php">Thống kê</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_orders.php">Quản lý Đơn Hàng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_books.php">Quản lý Sách</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_orders.php">Quản lý Đánh giá</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_publishers.php">Quản lý Nhà xuất bản</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_users.php">Quản lý Người Dùng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_accountAdmin.php">Admin</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="logout_admin.php">Đăng xuất</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <div class="header">
                    <h2>Chào Admin</h2>
                </div>
                <div class="stat-box">
                    <h3>Sách sắp hết hàng</h3>
                    <ul>
                        <?php if (empty($low_stock_books)) : ?>
                            <li>Không có sách nào sắp hết hàng.</li>
                        <?php else : ?>
                            <?php foreach ($low_stock_books as $book) : ?>
                                <li><?php echo $book['TenSach']; ?> - Còn lại: <?php echo $book['SoLuong']; ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="time-range-form">
                    <form method="post">
                        <label for="date_range">Chọn khoảng thời gian:</label>
                        <input type="text" id="date_range" name="date_range" class="form-control" style="display:inline-block; width:auto;" />
                        <button type="submit">Xem thống kê</button>
                    </form>
                </div>
                <div class="info-container row">
                    <div class="info-box left col-md-4">
                        <h4>Tổng số đơn hàng: <?php echo $count_orders; ?></h4>
                        <canvas id="orderChart"></canvas>
                    </div>
                    <div class="info-box right col-md-8">
                        <h4>Doanh thu ước tính: <?php echo number_format($revenue_estimated, 2); ?> VND</h4>
                        <h4>Doanh thu đã nhận: <?php echo number_format($revenue_received, 2); ?> VND</h4>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        $(function() {
            $('#date_range').daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY'
                },
                startDate: moment('<?php echo date("d/m/Y", strtotime($start_date)); ?>', 'DD/MM/YYYY'),
                endDate: moment('<?php echo date("d/m/Y", strtotime($end_date)); ?>', 'DD/MM/YYYY')
            });
        });

        const ctxOrder = document.getElementById('orderChart').getContext('2d');
        const orderChart = new Chart(ctxOrder, {
            type: 'doughnut',
            data: {
                labels: ['Đã giao', 'Chờ duyệt', 'Đang giao', 'Đã hủy'],
                datasets: [{
                    label: 'Thống kê đơn hàng',
                    data: [<?php echo $order_data['Completed']; ?>, <?php echo $order_data['Pending']; ?>, <?php echo $order_data['Processing']; ?>, <?php echo $order_data['Cancelled']; ?>],
                    backgroundColor: ['#4CAF50', '#FFC107', ' #434F90', '#F44336'],
                }]
            },

            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: 'black' // Đảm bảo nhãn dễ đọc
                        }
                    },
                    title: {
                        display: true,
                        text: 'Thống kê đơn hàng',
                        color: 'black' // Đổi màu chữ cho tiêu đề
                    }
                }
            },
        });

        const periods = <?php echo $periods_json; ?>;
        const formattedPeriods = periods.map(period => period.toString());

        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: formattedPeriods,
                datasets: [{
                    label: 'Doanh thu',
                    data: <?php echo $revenues_json; ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.5)',
                    borderColor: '#007BFF',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: 'black' // Đảm bảo nhãn dễ đọc
                        }
                    },
                    title: {
                        display: true,
                        text: 'Thống kê doanh thu',
                        color: 'black' // Đổi màu chữ cho tiêu đề
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Ngày',
                            color: 'black' // Đổi màu chữ cho trục X
                        },
                        ticks: {
                            color: 'black' // Đổi màu chữ cho các nhãn trục X
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Doanh thu',
                            color: 'black' // Đổi màu chữ cho trục Y
                        },
                        ticks: {
                            color: 'black', // Đổi màu chữ cho các nhãn trục Y
                            beginAtZero: true
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>