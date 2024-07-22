<?php
include 'db_connect.php'; // Kết nối CSDL

if (isset($_GET['sachID'])) {
    $sachID = $_GET['sachID'];
    $sql = "SELECT * FROM sach WHERE SachID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sachID);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if ($book) {
        echo '<div class="book-details">';
        echo '<h2>' . htmlspecialchars($book['TenSach']) . '</h2>';
        echo '<img src="' . htmlspecialchars($book['HinhAnh']) . '" alt="' . htmlspecialchars($book['TenSach']) . '">';
        echo '<p><strong>Giá: </strong>' . number_format($book['GiaBan']) . 'đ</p>';
        echo '<p><strong>Mô tả: </strong>' . htmlspecialchars($book['MoTa']) . '</p>';
        echo '<button class="add-to-cart" onclick="addToCart(' . $book['SachID'] . ');">Thêm vào giỏ</button>';
        echo '</div>';
    } else {
        echo '<p>Không tìm thấy thông tin sách.</p>';
    }

    $stmt->close();
}
$conn->close();
