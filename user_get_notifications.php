<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "Bạn cần đăng nhập để xem thông báo.";
    exit;
}

$userID = $_SESSION['userid'];

$sql = "SELECT NoiDung, NgayGio FROM ThongBao WHERE UserID = ? ORDER BY NgayGio DESC LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

$html = '';
while ($row = $result->fetch_assoc()) {
    $html .= '<div class="notification-item">';
    $html .= '<p>' . htmlspecialchars($row['NoiDung']) . '</p>';
    $html .= '<small>' . date("d-m-Y H:i:s", strtotime($row['NgayGio'])) . '</small>';
    $html .= '</div>';
}

echo $html;

// Đánh dấu thông báo là đã xem
$sql = "UPDATE ThongBao SET DaXem = 1 WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->close();
$conn->close();
