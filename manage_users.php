<?php
include 'db_connect.php'; // Ensure you have this file to connect to your database

// Pagination variables
$recordsPerPage = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Search keyword handling
$searchKeyword = '';
$searchParam = '';
$query = "SELECT * FROM nguoidung";

// If search keyword is provided, add search conditions
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $searchParam = "%$searchKeyword%";
    $query .= " WHERE TenDangNhap LIKE ? 
                OR Email LIKE ? 
                OR SoDienThoai LIKE ? 
                OR DiaChi LIKE ? 
                LIMIT ? OFFSET ?";
} else {
    $query .= " LIMIT ? OFFSET ?";
}

$stmt = $conn->prepare($query);

// Bind parameters for search
if (isset($_GET['search'])) {
    $stmt->bind_param("ssssii", $searchParam, $searchParam, $searchParam, $searchParam, $recordsPerPage, $offset);
} else {
    $stmt->bind_param("ii", $recordsPerPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM nguoidung";
if (isset($_GET['search'])) {
    $countQuery .= " WHERE TenDangNhap LIKE ? 
                     OR Email LIKE ? 
                     OR SoDienThoai LIKE ? 
                     OR DiaChi LIKE ?";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
} else {
    $countStmt = $conn->query($countQuery);
    $totalRecords = $countStmt->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $countStmt->close();
}

// Fetch total records when using prepared statements
if (isset($_GET['search'])) {
    $countStmt->execute();
    $totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $recordsPerPage);
    $countStmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
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
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }

        .form-control {
            margin-right: 5px;
        }

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

        /* Sidebar Styles */
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

        /* Media query for screen width greater than 769px */
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
        <h2>Quản Lý Người Dùng</h2>
        <!-- Form tìm kiếm người dùng -->
        <form class="form-inline" method="get" action="manage_users.php">
            <input class="form-control" type="search" name="search" placeholder="Tìm kiếm người dùng" aria-label="Search" value="<?php echo htmlspecialchars($searchKeyword); ?>">
            <button class="btn btn-outline-success" type="submit">Tìm kiếm</button>
        </form>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Email</th>
                        <th>Số Điện Thoại</th>
                        <th>Địa Chỉ</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $isActive = $row['IsActive'] ?? 1; // Default to 1 (Active)
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['TenDangNhap']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['SoDienThoai']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['DiaChi']) . "</td>";
                            echo "<td>
                                <button class='btn btn-warning btn-sm' id='button-{$row['ID']}' onclick='toggleLockUser(" . htmlspecialchars($row['ID']) . ")'>" . ($isActive ? "Khóa" : "Mở khóa") . "</button>
                              </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Không có người dùng nào</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <script>
        function toggleLockUser(userId) {
            var button = document.getElementById('button-' + userId);
            var confirmationMessage = button.textContent.includes("Khóa") ? 'Bạn có chắc chắn muốn khóa người dùng này không?' : 'Bạn có chắc chắn muốn mở khóa người dùng này không?';
            if (confirm(confirmationMessage)) {
                window.location.href = 'toggle_lock_user.php?user_id=' + userId;
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
