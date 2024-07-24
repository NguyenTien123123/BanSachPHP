<?php
include 'db_connect.php';

$editState = false;
$editPublisher = ['NXBID' => '', 'TenNXB' => '', 'DiaChi' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['action'] == "add") {
        $tenNXB = $_POST['tenNXB'];
        $diaChi = $_POST['diaChi'];
        $sql = "INSERT INTO nhaxuatban (TenNXB, DiaChi) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $tenNXB, $diaChi);
        if ($stmt->execute()) {
            header("Location: manage_publishers.php");
            exit();
        }
    } elseif ($_POST['action'] == "edit") {
        $nxbID = $_POST['nxbID'];
        $tenNXB = $_POST['tenNXB'];
        $diaChi = $_POST['diaChi'];
        $sql = "UPDATE nhaxuatban SET TenNXB = ?, DiaChi = ? WHERE NXBID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $tenNXB, $diaChi, $nxbID);
        if ($stmt->execute()) {
            header("Location: manage_publishers.php");
            exit();
        }
    } elseif ($_POST['action'] == "delete") {
        $nxbID = $_POST['nxbID'];
        $sql = "DELETE FROM nhaxuatban WHERE NXBID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $nxbID);
        if ($stmt->execute()) {
            header("Location: manage_publishers.php");
            exit();
        }
    } elseif ($_POST['action'] == "load") {
        $nxbID = $_POST['nxbID'];
        $sql = "SELECT * FROM nhaxuatban WHERE NXBID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $nxbID);
        $stmt->execute();
        $result = $stmt->get_result();
        $editPublisher = $result->fetch_assoc();
        $editState = true;
    }
}

$searchKeyword = '';
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $query = "SELECT * FROM nhaxuatban WHERE TenNXB LIKE '%$searchKeyword%' OR DiaChi LIKE '%$searchKeyword%'";
} else {
    $query = "SELECT * FROM nhaxuatban";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhà Xuất Bản</title>
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

        /* Sidebar Styles */
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
        <h2>Quản Lý Nhà Xuất Bản</h2>
        <button class="btn btn-primary btn-sm" onclick="window.location.href='admin_dashboard.php'">
            <i class="fas fa-home"></i>
        </button>
        <!-- Form tìm kiếm nhà xuất bản -->
        <form class="form-inline" method="get" action="manage_publishers.php">
            <input class="form-control" type="search" name="search" placeholder="Tìm kiếm nhà xuất bản" aria-label="Search" value="<?php echo htmlspecialchars($searchKeyword); ?>">
            <button class="btn btn-outline-success" type="submit">Tìm kiếm</button>
        </form>

        <!-- Form thêm hoặc cập nhật nhà xuất bản -->
        <form class="form-inline mb-4" method="post" action="manage_publishers.php">
            <input type="hidden" name="action" value="<?php echo $editState ? 'edit' : 'add'; ?>">
            <input type="hidden" name="nxbID" value="<?php echo htmlspecialchars($editPublisher['NXBID']); ?>">
            <input type="text" class="form-control" name="tenNXB" placeholder="Tên Nhà Xuất Bản" value="<?php echo htmlspecialchars($editPublisher['TenNXB']); ?>" required>
            <input type="text" class="form-control" name="diaChi" placeholder="Địa Chỉ" value="<?php echo htmlspecialchars($editPublisher['DiaChi']); ?>" required>
            <button type="submit" class="btn btn-<?php echo $editState ? 'warning' : 'success'; ?>"><?php echo $editState ? 'Cập Nhật' : 'Thêm Mới'; ?></button>
        </form>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Nhà Xuất Bản</th>
                        <th>Địa Chỉ</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['NXBID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['TenNXB']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['DiaChi']) . "</td>";
                        echo "<td>
                            <form method='post' class='d-inline' onsubmit='return confirmDelete()'>
                                <input type='hidden' name='action' value='delete'>
                                <input type='hidden' name='nxbID' value='" . htmlspecialchars($row['NXBID']) . "'>
                                <button type='submit' class='btn btn-danger btn-sm'>Xóa</button>
                            </form>
                            <form method='post' class='d-inline'>
                                <input type='hidden' name='action' value='load'>
                                <input type='hidden' name='nxbID' value='" . htmlspecialchars($row['NXBID']) . "'>
                                <button type='submit' class='btn btn-warning btn-sm'>Cập Nhật</button>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Bạn có chắc chắn muốn xóa mục này không?');
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>