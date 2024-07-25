<?php
include 'db_connect.php';
$user_id = $_GET['user_id'];

if ($user_id) {
    $query = "SELECT IsActive FROM nguoidung WHERE ID = $user_id";
    $isActive = $conn->query($query)->fetch_assoc()['IsActive'];
    $newStatus = $isActive ? 0 : 1;

    $sql = "UPDATE nguoidung SET IsActive = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newStatus, $user_id);
    $stmt->execute();
    $stmt->close();
}
header("Location: manage_users.php");
exit();
