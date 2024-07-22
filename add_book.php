<?php
include 'db_connect.php';

$tenSach = $_POST['tenSach'];
$giaBan = $_POST['giaBan'];
$soLuong = $_POST['soLuong'];
$moTa = $_POST['moTa'];
$theLoai = $_POST['theLoai'];

// Prepare an insert statement
$sql = $conn->prepare("INSERT INTO sach (TenSach, GiaBan, SoLuong, MoTa, TheLoai) VALUES (?, ?, ?, ?, ?)");
$sql->bind_param("sdisi", $tenSach, $giaBan, $soLuong, $moTa, $theLoai);

if ($sql->execute()) {
    echo "Thêm sách thành công";
} else {
    echo "Lỗi: " . $sql->error;
}

$sql->close();
$conn->close();
