<?php
session_start();
include 'db_connect.php'; // Đảm bảo bạn đã kết nối tới CSDL

$login_error = ''; // Khởi tạo biến lỗi trống

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Tạo câu truy vấn
    $sql = "SELECT ID, TenDangNhap, MatKhau, IsActive FROM nguoidung WHERE TenDangNhap = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Kiểm tra mật khẩu và xem tài khoản có bị khóa không
        if (password_verify($password, $user['MatKhau'])) {
            if ($user['IsActive']) {
                // Nếu tài khoản không bị khóa và mật khẩu đúng
                $_SESSION['loggedin'] = true;
                $_SESSION['userid'] = $user['ID'];
                $_SESSION['username'] = $user['TenDangNhap'];
                header("location: index.php"); // Chuyển hướng tới trang chủ
                exit;
            } else {
                $login_error = "Tài khoản của bạn đã bị khóa.";
            }
        } else {
            $login_error = "Mật khẩu không chính xác.";
        }
    } else {
        $login_error = "Không tìm thấy tên đăng nhập.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="path/to/your/custom.css"> <!-- Đảm bảo đường dẫn này đúng -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            /* Màu nền xanh nhạt */
            margin: 0;
            padding: 0;
        }

        .login-container {
            max-width: 450px;
            margin: 60px auto;
            padding: 30px 40px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #66cdaa;
            /* Màu xanh lá cây nhạt */
        }

        .login-container h2 {
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

        .error {
            color: #cc0000;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h2>Đăng nhập</h2>
            <div class="form-group">
                <label for="username">Số điện thoại/Email</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
            </div>
            <?php if ($login_error != '') { ?>
                <p class="error"><?php echo $login_error; ?></p>
            <?php } ?>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary btn-custom btn-block">Đăng nhập</button>
                <button type="button" class="btn btn-secondary btn-custom btn-block" onclick="window.location.href='register.php'">Đăng ký</button>
                <button type="button" class="btn btn-secondary btn-custom btn-block" onclick="location.href='index.php'">Quay lại trang chủ</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function togglePassword() {
            let passwordInput = document.getElementById('password');
            let toggleIcon = document.querySelector('.password-toggle');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.add('fa-eye-slash');
                toggleIcon.classList.remove('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>