<?php
session_start();

// Khởi tạo thông báo lỗi
$login_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Kiểm tra nếu các trường username và password có tồn tại trong mảng $_POST
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Kiểm tra thông tin đăng nhập
        if ($username == 'managers' && $password == 'managers') {
            // Đăng nhập thành công
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: managers.php"); // Chuyển đến dashboard admin
            exit;
        } else {
            // Thông tin đăng nhập sai
            $login_error = "Tên đăng nhập hoặc mật khẩu không chính xác";
        }
    } else {
        $login_error = "Vui lòng điền đầy đủ thông tin đăng nhập";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Managers</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #D8ACAC;
            border-radius: 5px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            color: #000000;
        }

        .form-control {
            border: 2px solid #D8ACAC;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 35px;
            cursor: pointer;
            color: #D8ACAC;
        }

        .btn-custom {
            width: 100%;
            padding: 10px 0;
            color: #fff;
            background-color: #20c997;
            /* Màu xanh ngọc */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #17a2b8;
        }

        .error {
            color: #CC0000;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container login-container">
        <h2 class="text-center">Managers</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group position-relative">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <span class="password-toggle" onclick="togglePassword()">&#128065;</span>
            </div>
            <?php if ($login_error != '') : ?>
                <p class="error"><?php echo $login_error; ?></p>
            <?php endif; ?>
            <div class="form-group">
                <button type="submit" class="btn btn-custom">Đăng nhập</button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var passwordToggle = document.querySelector(".password-toggle");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                passwordToggle.textContent = "🙈";
            } else {
                passwordField.type = "password";
                passwordToggle.textContent = "👁️‍🗨️";
            }
        }
    </script>
</body>

</html>