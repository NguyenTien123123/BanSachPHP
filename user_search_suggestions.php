<?php
include 'db_connect.php'; // Đảm bảo đã kết nối CSDL

if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $stmt = $conn->prepare("SELECT TenSach FROM sach WHERE TenSach LIKE CONCAT('%', ?, '%') LIMIT 10");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<ul>';
        while ($row = $result->fetch_assoc()) {
            echo '<li>' . htmlspecialchars($row['TenSach']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<ul><li>Không tìm thấy sách nào</li></ul>';
    }
}
