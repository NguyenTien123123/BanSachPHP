<?php
include 'db_connect.php';

$userID = $_POST['userID'];
$total = $_POST['total']; // Tổng tiền tính từ frontend hoặc backend
$items = json_decode($_POST['items'], true); // Danh sách các sản phẩm và số lượng

$sql = "INSERT INTO donhang (ID, NgayDatHang, TongTien, TrangThai) VALUES ('$userID', NOW(), '$total', 'Processing')";
if ($conn->query($sql) === TRUE) {
    $orderID = $conn->insert_id;
    foreach ($items as $item) {
        $sachID = $item['sachID'];
        $soLuong = $item['soLuong'];
        $donGia = $item['donGia'];
        $sql = "INSERT INTO chitietdonhang (DHID, SachID, SoLuong, DonGia) VALUES ('$orderID', '$sachID', '$soLuong', '$donGia')";
        $conn->query($sql);
    }
    echo "Order created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();