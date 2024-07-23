<?php
include 'db_connect.php'; // Kết nối CSDL

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if (isset($_POST['action'])) {
    $orderID = $_POST['orderID'];
    switch ($_POST['action']) {
        case "update":
            $newStatus = $_POST['status'];
            $sql = "UPDATE donhang SET TrangThai = ? WHERE DHID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newStatus, $orderID);
            $stmt->execute();

            // Thêm thông báo khi cập nhật trạng thái đơn hàng
            $sql = "SELECT ID FROM donhang WHERE DHID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $userID = $row['ID'];
                $noiDung = "Trạng thái đơn hàng của bạn đã được cập nhật thành " . $newStatus;
                $sql = "INSERT INTO ThongBao (UserID, NoiDung) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $userID, $noiDung);
                $stmt->execute();
            }
            break;
    }
    // Chuyển hướng để tránh re-post khi refresh
    header("Location: manage_orders.php");
    exit();
}

$date_filter_start = '';
$date_filter_end = '';
$status_filter = '';

if (isset($_POST['filter_date_start']) && isset($_POST['filter_date_end'])) {
    $date_filter_start = $_POST['filter_date_start'];
    $date_filter_end = $_POST['filter_date_end'];
}

if (isset($_POST['status_filter'])) {
    $status_filter = $_POST['status_filter'];
}

$query = "SELECT DHID, TrangThai, NgayDatHang, TongTien, PhuongThucThanhToan FROM donhang WHERE 1";

if ($date_filter_start && $date_filter_end) {
    $query .= " AND DATE(NgayDatHang) BETWEEN '$date_filter_start' AND '$date_filter_end'";
}

if ($status_filter) {
    $query .= " AND TrangThai = '$status_filter'";
}

$query .= " ORDER BY NgayDatHang DESC LIMIT 30";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f4f4f4;
            padding-top: 20px;
        }

        .container {
            margin-top: 40px;
            margin-bottom: 40px;
            max-width: 90%;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border-left: 5px solid #20c997;
            /* Màu xanh ngọc */
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        .btn {
            margin: 5px;
        }

        .form-inline {
            justify-content: center;
        }

        .form-control {
            margin: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Quản Lý Đơn Hàng</h2>
        <button class="btn btn-primary" onclick="window.location.href='admin_dashboard.php'">Quay lại Dashboard</button>
        <form method="post" class="form-inline mb-3">
            <label for="filter_date_start" class="mr-2">Từ ngày:</label>
            <input type="date" id="filter_date_start" name="filter_date_start" class="form-control mr-2" value="<?php echo htmlspecialchars($date_filter_start); ?>">
            <label for="filter_date_end" class="mr-2">Đến ngày:</label>
            <input type="date" id="filter_date_end" name="filter_date_end" class="form-control mr-2" value="<?php echo htmlspecialchars($date_filter_end); ?>">
            <label for="status_filter" class="mr-2">Trạng thái:</label>
            <select id="status_filter" name="status_filter" class="form-control mr-2">
                <option value="">Tất cả</option>
                <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Processing" <?php echo $status_filter == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                <option value="RefundedSuccessfully" <?php echo $status_filter == 'RefundedSuccessfully' ? 'selected' : ''; ?>>RefundedSuccessfully</option>
            </select>
            <button type="submit" class="btn btn-primary">Lọc</button>
        </form>
        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID Đơn Hàng</th>
                        <th>Ngày Đặt Hàng</th>
                        <th>Tổng Tiền</th>
                        <th>Thanh Toán</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['DHID']); ?></td>
                            <td><?php echo date("d-m-Y", strtotime($row['NgayDatHang'])); ?></td>
                            <td><?php echo number_format($row['TongTien'], 2); ?> VND</td>
                            <td><?php echo isset($row['PhuongThucThanhToan']) ? htmlspecialchars($row['PhuongThucThanhToan']) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($row['TrangThai']); ?></td>
                            <td>
                                <a href="order_details.php?orderID=<?php echo htmlspecialchars($row['DHID']); ?>" class="btn btn-info btn-sm">Xem Chi Tiết</a>
                                <form action="manage_orders.php" method="post" class="d-inline">
                                    <input type="hidden" name="orderID" value="<?php echo htmlspecialchars($row['DHID']); ?>">
                                    <input type="hidden" name="action" value="update">
                                    <select name="status" class="form-control form-control-sm d-inline" style="width: auto;">
                                        <option value="Pending" <?php echo $row['TrangThai'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo $row['TrangThai'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Completed" <?php echo $row['TrangThai'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo $row['TrangThai'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="RefundedSuccessfully" <?php echo $row['TrangThai'] == 'RefundedSuccessfully' ? 'selected' : ''; ?>>RefundedSuccessfully</option>
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm">Cập Nhật</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>