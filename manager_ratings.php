<?php
include 'db_connect.php'; // Kết nối CSDL

// Thêm phần xử lý khi có hành động update
if (isset($_POST['action'])) {
    $ratingID = $_POST['ratingID'];
    switch ($_POST['action']) {
        case "update":
            $newStatus = $_POST['status'];
            $sql = "UPDATE ratings SET status = ? WHERE id_rat = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newStatus, $ratingID);
            $stmt->execute();
            break;
    }
    // Chuyển hướng để tránh re-post khi refresh
    header("Location: manager_ratings.php");
    exit();
}

// Lấy dữ liệu từ form lọc
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

// Lấy dữ liệu từ bảng ratings
$query = "SELECT r.id_rat, s.TenSach, r.rating, r.created_at, r.comment, r.status 
          FROM ratings r 
          JOIN sach s ON r.SachID = s.SachID 
          WHERE 1";

if ($date_filter_start && $date_filter_end) {
    $query .= " AND DATE(r.created_at) BETWEEN '$date_filter_start' AND '$date_filter_end'";
}

if ($status_filter) {
    $query .= " AND r.status = '$status_filter'";
}

$query .= " ORDER BY r.created_at DESC LIMIT 30";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f4f4f4;
            padding-top: 20px;
            margin-left: 270px;
            /* Adjust according to the sidebar width */
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



        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            body {
                margin-left: 0;
            }
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

    <div class="container">
        <h2>Quản Lý Đánh Giá</h2>
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
                <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
            </select>
            <button type="submit" class="btn btn-primary">Lọc</button>
        </form>
        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID Đánh giá</th>
                        <th>Tên sách</th>
                        <th>Đánh giá</th>
                        <th>Ngày đánh giá</th>
                        <th>Ghi chú</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_rat']); ?></td>
                            <td><?php echo htmlspecialchars($row['TenSach']); ?></td>
                            <td><?php echo htmlspecialchars($row['rating']); ?></td>
                            <td><?php echo date("d-m-Y", strtotime($row['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['comment']); ?></td>
                            <td>
                                <form action="manager_ratings.php" method="post" class="d-inline">
                                    <input type="hidden" name="ratingID" value="<?php echo htmlspecialchars($row['id_rat']); ?>">
                                    <input type="hidden" name="action" value="update">
                                    <?php if ($row['status'] != 'Approved') : ?>
                                        <select name="status" class="form-control form-control-sm d-inline" style="width: auto;">
                                            <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Approved" <?php echo $row['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        </select>
                                        <button type="submit" class="btn btn-success btn-sm">Duyệt</button>
                                    <?php else : ?>
                                        <select name="status" class="form-control form-control-sm d-inline" style="width: auto;" disabled>
                                            <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Approved" <?php echo $row['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        </select>
                                    <?php endif; ?>
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