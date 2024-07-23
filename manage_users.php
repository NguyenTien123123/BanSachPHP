<?php
include 'db_connect.php'; // Ensure you have this file to connect to your database

$searchKeyword = '';
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $query = "SELECT * FROM nguoidung WHERE TenDangNhap LIKE '%$searchKeyword%' OR Email LIKE '%$searchKeyword%' OR SoDienThoai LIKE '%$searchKeyword%' OR DiaChi LIKE '%$searchKeyword%'";
} else {
    $query = "SELECT * FROM nguoidung";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
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
            display: flex;
            justify-content: flex-start;
            /* Align form to the left */
            margin-bottom: 20px;
            /* Add some space below the form */
        }

        .form-control {
            margin-right: 5px;
            /* Space between input and button */
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Quản Lý Người Dùng</h2>
        <button class="btn btn-primary mb-3" onclick="window.location.href='admin_dashboard.php'">Quay lại Dashboard</button>

        <!-- Form tìm kiếm người dùng -->
        <form class="form-inline" method="get" action="manager_user.php">
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