<?php
include 'db_connect.php'; // Ensure you have this file to connect to the database

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $email = $_POST['email'];

    // Check if the username already exists
    $check_sql = "SELECT TenDangNhap FROM nguoidung WHERE TenDangNhap = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $message = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên đăng nhập khác.";
    } else {
        // If username does not exist, proceed to insert
        $sql = "INSERT INTO nguoidung (TenDangNhap, MatKhau, Email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password, $email);

        if ($stmt->execute()) {
            $message = "Tạo tài khoản thành công. Vui lòng chọn đăng nhập.";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
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
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #4682b4;
            font-weight: bold;
        }

        .form-group label {
            font-weight: bold;
            color: #4682b4;
        }

        .form-control {
            border: 2px solid #b0c4de;
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
            border: none;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #3cb371;
        }

        .btn-secondary {
            background-color: #ffcc99;
            border: none;
            color: #000;
        }

        .btn-secondary:hover {
            background-color: #ffb366;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <?php if ($message) : ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h2>Đăng ký</h2>
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary btn-custom btn-block">Đăng ký</button>
                <button type="button" class="btn btn-secondary btn-custom btn-block" onclick="window.location.href='login.php'">Đăng nhập</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>