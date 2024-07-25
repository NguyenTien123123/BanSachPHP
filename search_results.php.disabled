<?php
include 'db_connect.php'; // Đảm bảo kết nối tới CSDL đã được thiết lập

$search = $_GET['search'];

// Tạo và thực thi truy vấn SQL
$sql = "SELECT * FROM sach WHERE TenSach LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $search . "%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Tìm Kiếm</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Kết Quả Tìm Kiếm cho "<?php echo htmlspecialchars($search); ?>"</h1>
    <div class="search-results">
        <?php if ($result->num_rows > 0) : ?>
            <ul>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <li><?php echo htmlspecialchars($row['TenSach']); ?> - Giá: <?php echo htmlspecialchars($row['GiaBan']); ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else : ?>
            <p>Không tìm thấy kết quả nào.</p>
        <?php endif; ?>
    </div>
</body>

</html>