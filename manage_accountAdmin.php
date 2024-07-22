<?php
include 'db_connect.php'; // Đảm bảo bạn đã tạo và có file này để kết nối CSDL

$searchKeyword = '';
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $query = "SELECT * FROM accountAdmin WHERE TenDangNhap LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$searchKeyword%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT * FROM accountAdmin";
    $result = $conn->query($query);
}

// Xử lý xóa tài khoản
if (isset($_POST['delete_admin_id'])) {
    $admin_id = $_POST['delete_admin_id'];

    $sql = "DELETE FROM accountAdmin WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    if (!$stmt->execute()) {
        echo "Lỗi khi xóa admin: " . $stmt->error;
    } else {
        echo "<script>
                alert('Đã xóa admin thành công.');
                window.location.href = 'manage_accountAdmin.php';
              </script>";
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f4f4f4;
            padding-top: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    </style>
    <script>
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
    <div class="container">
        <h2>Quản Lý Người Dùng</h2>
        <button class="btn btn-primary mb-3" onclick="window.location.href='admin_dashboard.php'">Quay lại Dashboard</button>
        <button class="btn btn-primary mb-3" onclick="window.location.href='registerAdmin.php'">Tạo</button>

        <!-- Form tìm kiếm người dùng -->
        <form class="form-inline mb-3" method="get" action="manage_accountAdmin.php">
            <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm kiếm admin" aria-label="Search" value="<?php echo htmlspecialchars($searchKeyword); ?>">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Tìm kiếm</button>
        </form>

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
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
