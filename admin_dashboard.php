<?php
session_start();
include 'db_connect.php'; // Kết nối tới CSDL

// Kiểm tra xem admin đã đăng nhập chưa
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        .sidebar {
            background-color: #2e8b57;
            color: #ecf0f1;
            padding: 20px;
            height: 100vh;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
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
            flex: 1 1 calc(50% - 20px); /* Two boxes per row with margin */
            height: 200px; /* Fixed height for uniformity */
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
            background-color: #3cb371; /* Default background color */
        }

        .info-box.left, .info-box.right {
            background-color: #3cb371; /* Adjust background color if needed */
        }

        .info-box h4 {
            color: #fff;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .info-box {
                flex: 1 1 100%; /* Full width for each box on smaller screens */
                height: 150px; /* Adjust height for mobile screens */
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
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
                            <a class="nav-link" href="manage_reviews.php">Quản lý Đánh giá</a>
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
            <main class="col-md-10 ml-sm-auto col-lg-10 px-4">
                <div class="header">
                    <h2>Dashboard</h2>
                </div>
                <div class="info-container">
                    <div class="info-box left">
                        <h4>Quản lý Đơn hàng</h4>
                    </div>
                    <div class="info-box right">
                        <h4>Quản lý Sách</h4>
                    </div>
                    <div class="info-box left">
                        <h4>Quản lý đánh giá</h4>
                    </div>
                    <div class="info-box right">
                        <h4>Quản lý Nhà Xuất Bản</h4>
                    </div>
                    <div class="info-box left">
                        <h4>Quản lý Người Dùng</h4>
                    </div>
                    <div class="info-box right">
                        <h4>Quản lý Admin</h4>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
