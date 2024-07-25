<?php
session_start();
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (!isset($_SESSION['username'])) {
    die("Bạn chưa đăng nhập."); // Kiểm tra nếu người dùng chưa đăng nhập
}

$username = $_SESSION['username']; // Lấy tên đăng nhập từ session

// Khởi tạo các biến để chứa dữ liệu hiện tại
$hoten = $sodienThoai = $diachi = "";

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoten = $_POST['hoten'];
    $sodienThoai = $_POST['sodienThoai'];
    $diachi = $_POST['diachi'];

    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    $sql = "UPDATE nguoidung SET HoTen = ?, SoDienThoai = ?, DiaChi = ? WHERE TenDangNhap = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
    }

    $stmt->bind_param("ssss", $hoten, $sodienThoai, $diachi, $username);

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thông tin thành công');window.location='index.php';</script>";
    } else {
        echo "<script>alert('Lỗi: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Lấy thông tin hiện tại
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    $sql = "SELECT HoTen, SoDienThoai, DiaChi FROM nguoidung WHERE TenDangNhap = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hoten, $sodienThoai, $diachi);
    $stmt->fetch();
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin</title>
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
            <h2>Cập nhật thông tin</h2>

            <div class="form-group">
                <label for="hoten">Họ và Tên:</label>
                <input type="text" class="form-control" id="hoten" name="hoten" value="<?php echo htmlspecialchars($hoten); ?>" required>
            </div>
            <div class="form-group">
                <label for="sodienThoai">SDT:</label>
                <input type="text" class="form-control" id="sodienThoai" name="sodienThoai" value="<?php echo htmlspecialchars($sodienThoai); ?>" required>
            </div>
            <div class="form-group">
                <label for="diachi">Địa chỉ giao hàng:</label>
                <input type="text" class="form-control" id="diachi" name="diachi" value="<?php echo htmlspecialchars($diachi); ?>" required>
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary btn-custom btn-block">Thay đổi</button>
                <button type="button" class="btn btn-secondary btn-custom btn-block" onclick="location.href='index.php'">Quay lại</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>