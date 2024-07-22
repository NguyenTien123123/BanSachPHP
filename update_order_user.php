
<?php
session_start();
include 'db_connect.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';

    if (empty($id) || empty($address)) {
        echo 'error:Invalid input';
        exit;
    }

    // Prepare and execute update statement
    $sql = "UPDATE donhang SET DiaChiGiaoHang = ? WHERE DHID = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo 'error:Failed to prepare statement';
        exit;
    }

    $stmt->bind_param("si", $address, $id);
    $result = $stmt->execute();

    if ($result) {
        if ($stmt->affected_rows > 0) {
            echo 'success';
        } else {
            echo 'no_change';
        }
    } else {
        echo 'error:Update failed';
    }

    $stmt->close();
    $conn->close();
}
?>