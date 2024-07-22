<?php
session_start();
include 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (!isset($_SESSION['userid'])) {
    header("Location: login.php"); // Chuyển hướng nếu người dùng chưa đăng nhập
    exit;
}

$orderID = $_POST['id'];

// Xác thực đầu vào
if (empty($orderID) || !is_numeric($orderID)) {
    echo 'error:Invalid order ID';
    exit;
}

// Cập nhật trạng thái đơn hàng thành "Cancelled"
$sql_update_status = "UPDATE donhang SET TrangThai = 'Cancelled' WHERE DHID = ? AND TrangThai = 'Pending'";
$stmt_update_status = $conn->prepare($sql_update_status);
$stmt_update_status->bind_param("i", $orderID);
$stmt_update_status->execute();

if ($stmt_update_status->affected_rows > 0) {
    echo 'success';
} else {
    echo 'error:No rows updated or order already cancelled';
}

$stmt_update_status->close();
$conn->close();
?>
