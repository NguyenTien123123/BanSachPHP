<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    echo "Bạn cần đăng nhập để thực hiện chức năng này!";
    exit;
}

$userID = $_SESSION['userid'];
$sachID = $_POST['sachID'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($quantity < 1) {
    echo "Số lượng phải lớn hơn hoặc bằng 1";
    exit;
}

// Kiểm tra số lượng sách tồn kho
$sql = "SELECT TenSach, SoLuong FROM sach WHERE SachID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sachID);
$stmt->execute();
$result = $stmt->get_result();
$sach = $result->fetch_assoc();

if (!$sach) {
    echo "Sách không tồn tại";
    exit;
}

if ($sach['SoLuong'] >= $quantity) {
    // Kiểm tra xem sách đã có trong giỏ hàng chưa
    $sql = "SELECT * FROM giohang WHERE ID = ? AND SachID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userID, $sachID);
    $stmt->execute();
    $result = $stmt->get_result();
    $gioHang = $result->fetch_assoc();

    if ($gioHang) {
        // Cập nhật số lượng sách trong giỏ hàng
        $newQuantity = $gioHang['SoLuong'] + $quantity;
        if ($newQuantity > $sach['SoLuong']) {
            echo "Không đủ số lượng sách trong kho";
            exit;
        }

        $sql = "UPDATE giohang SET SoLuong = ? WHERE ID = ? AND SachID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $newQuantity, $userID, $sachID);
    } else {
        // Thêm sách vào giỏ hàng
        $sql = "INSERT INTO giohang (ID, SachID, SoLuong) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $userID, $sachID, $quantity);
    }
    $stmt->execute();

    echo "Sách '{$sach['TenSach']}' đã được thêm vào giỏ hàng!";
} else {
    echo "Sách '{$sach['TenSach']}' đã hết hàng.";
}
