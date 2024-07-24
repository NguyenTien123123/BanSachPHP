<?php
include 'db_connect.php'; // Ensure this file exists to connect to the database

if (isset($_POST['action'])) {
    $orderID = $_POST['orderID'];
    switch ($_POST['action']) {
        case "update":
            $newStatus = $_POST['status'];
            $sql = "UPDATE donhang SET TrangThai = ? WHERE DHID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newStatus, $orderID);
            if ($stmt->execute()) {
                // Add notification
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
            } else {
                // Handle the error
                $error = "Lỗi khi cập nhật trạng thái: " . $stmt->error;
            }
            $stmt->close();
            break;
    }
    // Redirect to avoid re-post on refresh
    header("Location: manage_orders.php" . (isset($error) ? "?error=" . urlencode($error) : ""));
    exit();
}

// Filters
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
    $query .= " AND DATE(NgayDatHang) BETWEEN ? AND ?";
}

if ($status_filter) {
    $query .= " AND TrangThai = ?";
}

$query .= " ORDER BY NgayDatHang DESC LIMIT 30";

$stmt = $conn->prepare($query);
if ($date_filter_start && $date_filter_end) {
    $stmt->bind_param("ss", $date_filter_start, $date_filter_end);
} elseif ($status_filter) {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f4f4f4;
            padding-top: 20px;
        }

        .container {
            margin-top: 5px;
            margin-bottom: 5px;
            max-width: 100%;
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

        /* Default styles for sidebar and main content */
        .sidebar {
            background-color: white;
            color: #ecf0f1;
            padding: 20px;
            height: 100vh;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
            overflow-y: auto;
        }

        .main-content {
            margin-left: 270px;
            /* Adjusted for the width of the sidebar */
        }

        /* Media query for screen width 768px or less */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                /* Remove margin when sidebar is hidden */
                width: 100%;
                /* Make main content area full width */
            }
        }

        /* Media query for screen width 768px or less */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
                /* Remove margin when sidebar is hidden */
                width: 100%;
                /* Make main content area full width */
            }
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


        /* .main-content {
            margin-left: 270px;
        } */

        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border-radius: 5px;
            z-index: 1000;
        }

        .notification.error {
            background-color: #dc3545;
        }

        @media (min-width: 769px) {
            .btn-hide-lg {
                display: none;
            }
        }
    </style>
</head>

<body>
    <nav class="sidebar">
        <div class="sidebar-sticky">
            <h1>Admin Dashboard</h1>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin_report.php">Thống kê</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_orders.php">Quản lý Đơn Hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_books.php">Quản lý Sách</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manager_ratings.php">Quản lý Đánh giá</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_publishers.php">Quản lý Nhà xuất bản</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">Quản lý Người Dùng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_accountAdmin.php">Admin</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout_admin.php">Đăng xuất</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="main-content">
        <div class="container">
            <h2>Quản Lý Đơn Hàng</h2>
            <button class="btn btn-primary btn-sm btn-hide-lg" onclick="window.location.href='admin_dashboard.php'">
                <i class="fas fa-home"></i>
            </button>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function showNotification(message, isError = false) {
            const notification = document.createElement('div');
            notification.className = 'notification' + (isError ? ' error' : '');
            notification.textContent = message;
            document.body.appendChild(notification);
            notification.style.display = 'block';
            setTimeout(() => notification.style.display = 'none', 3000);
        }

        <?php if (isset($error)) : ?>
            showNotification('<?php echo htmlspecialchars($error); ?>', true);
        <?php endif; ?>
    </script>
</body>

</html>