<?php
include 'db_connect.php'; // Đảm bảo bạn đã có file này để kết nối đến CSDL

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới và xác nhận mật khẩu
    if ($new_password !== $confirm_password) {
        echo "<script>alert('Mật khẩu mới và xác nhận mật khẩu không khớp.');</script>";
    } else {
        // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
        $sql = "SELECT MatKhau FROM nguoidung WHERE TenDangNhap = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo "<script>alert('Tài khoản không tồn tại.');</script>";
        } else {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();

            // Kiểm tra mật khẩu cũ
            if (password_verify($old_password, $hashed_password)) {
                // Mã hóa mật khẩu mới và cập nhật cơ sở dữ liệu
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE nguoidung SET MatKhau = ? WHERE TenDangNhap = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_password_hashed, $username);

                if ($update_stmt->execute()) {
                    echo "<script>alert('Đổi mật khẩu thành công.');window.location='user_login.php';</script>";
                } else {
                    echo "<script>alert('Lỗi: " . $update_stmt->error . "');</script>";
                }

                $update_stmt->close();
            } else {
                echo "<script>alert('Mật khẩu cũ không chính xác.');</script>";
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            /* Màu nền xanh nhạt */
            margin: 0;
            padding: 0;
        }

        .register-container {
            max-width: 450px;
            margin: 60px auto;
            padding: 30px 40px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #66cdaa;
            /* Màu xanh lá cây nhạt */
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #4682b4;
            /* Màu xanh dương nhạt */
            font-weight: bold;
        }

        .form-group label {
            font-weight: bold;
            color: #4682b4;
            /* Màu xanh dương nhạt */
        }

        .form-control {
            border: 2px solid #b0c4de;
            /* Màu xanh dương nhạt hơn */
            border-radius: 30px;
            padding-left: 20px;
        }

        .btn-custom {
            border-radius: 30px;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .btn-primary {
            background-color: #66cdaa;
            /* Màu xanh lá cây nhạt */
            border: none;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #3cb371;
            /* Màu xanh lá cây đậm hơn */
        }

        .btn-secondary {
            background-color: #ffcc99;
            /* Màu cam nhạt */
            border: none;
            color: #000;
        }

        .btn-secondary:hover {
            background-color: #ffb366;
            /* Màu cam đậm hơn */
        }
    </style>
</head>

<body>
    <div class="register-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h2>Đổi mật khẩu</h2>
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="old_password">Mật khẩu cũ:</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Mật khẩu mới:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Nhập lại mật khẩu mới:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary btn-custom btn-block">Đổi mật khẩu</button>
                <button type="button" class="btn btn-secondary btn-custom btn-block" onclick="location.href='index.php'">Quay lại</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>