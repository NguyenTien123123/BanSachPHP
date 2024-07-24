<?php
include 'db_connect.php'; // Ensure this file exists to connect to the database

// Pagination variables
$recordsPerPage = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Search functionality
$searchKeyword = '';
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $query = "SELECT * FROM accountAdmin WHERE TenDangNhap LIKE ? LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$searchKeyword%";
    $stmt->bind_param("sii", $searchTerm, $recordsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT * FROM accountAdmin LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $recordsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Count total records for pagination
$countQuery = "SELECT COUNT(*) as total FROM accountAdmin";
$countResult = $conn->query($countQuery);
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Xử lý xóa tài khoản
if (isset($_POST['delete_admin_id'])) {
    $admin_id = $_POST['delete_admin_id'];

    $sql = "DELETE FROM accountAdmin WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    if (!$stmt->execute()) {
        $deleteError = "Lỗi khi xóa admin: " . $stmt->error;
    } else {
        $deleteSuccess = "Đã xóa admin thành công.";
    }
    $stmt->close();
}

// Xử lý khóa/mở khóa tài khoản
if (isset($_GET['admin_id'])) {
    $user_id = intval($_GET['admin_id']);

    if ($user_id) {
        $query = "SELECT IsActive FROM accountAdmin WHERE ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $isActive = $stmt->get_result()->fetch_assoc()['IsActive'];
        $newStatus = $isActive ? 0 : 1;

        $sql = "UPDATE accountAdmin SET IsActive = ? WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $newStatus, $user_id);
        $stmt->execute();
        $stmt->close();

        header('Location: manage_accountAdmin.php');
        exit();
    }
}

// Tạo tài khoản mới cho bảng accountAdmin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_account'])) {
    $username = $_POST['TenDangNhap'];
    $password = password_hash($_POST['MatKhau'], PASSWORD_DEFAULT); // Mã hóa mật khẩu

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $checkQuery = "SELECT * FROM accountAdmin WHERE TenDangNhap = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $createError = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
    } else {
        $sql = "INSERT INTO accountAdmin (TenDangNhap, MatKhau) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $createSuccess = "Tạo tài khoản thành công";
        } else {
            $createError = "Lỗi: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();

    header('Location: manage_accountAdmin.php?createSuccess=' . urlencode($createSuccess) . '&createError=' . urlencode($createError));
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            /* Màu xanh ngọc */
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-inline {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
        }

        .form-control {
            margin-right: 5px;
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

        /* Default styles for sidebar and main content */
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
    <script>
        function showNotification(message, isError = false) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.classList.toggle('error', isError);
            notification.style.display = 'block';

            setTimeout(() => {
                notification.style.display = 'none';
            }, 2000);
        }

        function confirmDelete(adminId) {
            if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
                document.getElementById('delete-form-' + adminId).submit();
            }
        }

        function toggleLockUser(adminId) {
            var button = document.getElementById('button-' + adminId);
            var confirmationMessage = button.textContent.includes("Khóa") ? 'Bạn có chắc chắn muốn khóa người dùng này không?' : 'Bạn có chắc chắn muốn mở khóa người dùng này không?';
            if (confirm(confirmationMessage)) {
                window.location.href = 'manage_accountAdmin.php?admin_id=' + adminId;
            }
        }
    </script>
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
        <h2>Admin</h2>
        <button class="btn btn-primary btn-sm btn-hide-lg" onclick="window.location.href='admin_dashboard.php'">
            <i class="fas fa-home"></i>
        </button>
        <!-- Form tìm kiếm người dùng -->
        <form class="form-inline mb-3" method="get" action="manage_accountAdmin.php">
            <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm kiếm admin" aria-label="Search" value="<?php echo htmlspecialchars($searchKeyword); ?>">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Tìm kiếm</button>
        </form>
        <form class="form-inline mb-3" method="post" action="manage_accountAdmin.php">
            <input type="text" class="form-control" name="TenDangNhap" placeholder="Tên đăng nhập" required>
            <input type="password" class="form-control" name="MatKhau" placeholder="Mật khẩu" required>
            <button class="btn btn-outline-success" type="submit" name="create_account">Tạo mới</button>
        </form>

        <div id="notification" class="notification"></div>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $isActive = $row['IsActive'] ?? 1; // Set default to 1 (Active)
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['TenDangNhap']) . "</td>";
                            echo "<td>
                                <form id='delete-form-" . htmlspecialchars($row['ID']) . "' method='post' action='manage_accountAdmin.php' style='display:inline;'>
                                    <input type='hidden' name='delete_admin_id' value='" . htmlspecialchars($row['ID']) . "'>
                                    <button type='button' class='btn btn-danger btn-sm' onclick='confirmDelete(" . htmlspecialchars($row['ID']) . ")'>Xóa</button>
                                </form>
                                <button class='btn btn-warning btn-sm' id='button-" . htmlspecialchars($row['ID']) . "' onclick='toggleLockUser(" . htmlspecialchars($row['ID']) . ")'>" . ($isActive ? "Khóa" : "Mở khóa") . "</button>
                              </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Không có người dùng nào</td></tr>";
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


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <?php if (isset($_GET['createSuccess']) && $_GET['createSuccess']) : ?>
        <script>
            showNotification('<?php echo htmlspecialchars($_GET['createSuccess']); ?>');
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['createError']) && $_GET['createError']) : ?>
        <script>
            showNotification('<?php echo htmlspecialchars($_GET['createError']); ?>', true);
        </script>
    <?php endif; ?>
</body>

</html>