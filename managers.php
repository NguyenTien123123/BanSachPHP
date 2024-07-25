<?php
session_start();
include 'db_connect.php'; // Kết nối tới CSDL

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: login_manager.php");
    exit;
}

// Truy vấn tổng số tài khoản, số tài khoản còn hoạt động và số tài khoản ngừng hoạt động
$sql = "SELECT 
            COUNT(*) AS TongTaiKhoan, 
            SUM(CASE WHEN IsActive = 1 THEN 1 ELSE 0 END) AS ConHoatDong, 
            SUM(CASE WHEN IsActive = 0 THEN 1 ELSE 0 END) AS NgungHoatDong 
        FROM nguoidung";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

$tong_tai_khoan = $row['TongTaiKhoan'];
$con_hoat_dong = $row['ConHoatDong'];
$ngung_hoat_dong = $row['NgungHoatDong'];




// Truy vấn tổng số tài khoản, số tài khoản còn hoạt động và số tài khoản ngừng hoạt động
$sql = "SELECT 
            COUNT(*) AS TongTaiKhoanAd, 
            SUM(CASE WHEN IsActive = 1 THEN 1 ELSE 0 END) AS ConHoatDongAd, 
            SUM(CASE WHEN IsActive = 0 THEN 1 ELSE 0 END) AS NgungHoatDongAd 
        FROM accountadmin";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

$tong_tai_khoanAd = $row['TongTaiKhoanAd'];
$con_hoat_dongAd = $row['ConHoatDongAd'];
$ngung_hoat_dongAd = $row['NgungHoatDongAd'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Managers Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        .sidebar {
            background-color: white;
            color: #ecf0f1;
            padding: 20px;
            height: 100vh;
            /* 85% of the viewport height */
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
            overflow-y: auto;
            /* Enable vertical scrolling */
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

        .content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 70px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            color: #3cb371;
        }

        .stat-box {
            background-color: #3cb371;
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-box h3 {
            margin: 0;
            font-size: 1.5em;
        }

        .stat-box ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .stat-box ul li {
            margin: 10px 0;
        }

        .time-range-form {
            text-align: center;
            margin-bottom: 20px;
        }

        .time-range-form select {
            padding: 5px;
            margin: 0 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .time-range-form button {
            padding: 5px 10px;
            background-color: #3cb371;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .time-range-form button:hover {
            background-color: #2e8b57;
        }

        .info-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 20px;
        }

        .info-box {
            flex: 1 1 calc(50% - 20px);
            height: 200px;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: #3cb371;
            text-decoration: none !important; /* Loại bỏ gạch chân */
        }

        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .info-box {
                flex: 1 1 100%;
                /* Full width for each box on smaller screens */
                height: 150px;
                /* Adjust height for mobile screens */
            }

        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="sidebar">
                <div class="sidebar-sticky">
                    <h1>Manager Dashboard</h1>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin_report.php">Thống kê</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">Quản lý Người Dùng</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_accountAdmin.php">Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login_managers.php">Đăng xuất</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-10 ml-sm-auto col-lg-10 px-4">
                <div class="header">
                    <h2>Dashboard</h2>
                </div>
                <div class="info-container">
                    <a href="manage_users.php" class="info-box">
                        <div>
                            <h4>Người dùng</h4>
                            <h6>Tài khoản: <?php echo $tong_tai_khoan; ?></h6>
                            <h6>Còn hoạt động: <?php echo $con_hoat_dong; ?></h6>
                            <h6>Ngừng hoạt động: <?php echo $ngung_hoat_dong; ?></h6>
                        </div>
                    </a>
                    <a href="manage_accountAdmin.php" class="info-box">
                        <div>
                            <h4>Nhân viên</h4>
                            <h6>Tài khoản: <?php echo $tong_tai_khoanAd; ?></h6>
                            <h6>Còn hoạt động: <?php echo $con_hoat_dongAd; ?></h6>
                            <h6>Ngừng hoạt động: <?php echo $ngung_hoat_dongAd; ?></h6>
                        </div>
                    </a>
                </div>
            </main>
        </div>
    </div>
</body>

</html>