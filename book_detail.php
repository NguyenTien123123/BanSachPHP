<?php
session_start();
include 'db_connect.php'; // Kết nối CSDL

// Lấy ID sách từ GET request
$sachID = isset($_GET['SachID']) ? $_GET['SachID'] : die('ERROR: Không tìm thấy sách.');

// Truy vấn thông tin sách từ CSDL
$sql = "SELECT s.*, n.TenNXB FROM sach s LEFT JOIN nhaxuatban n ON s.NXBID = n.NXBID WHERE s.SachID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sachID);
$stmt->execute();
$result = $stmt->get_result();
$sach = $result->fetch_assoc();

// Kiểm tra nếu sách không tồn tại
if (!$sach) {
    echo "<h2>Sách này không tồn tại!</h2>";
    exit();
}

// Truy vấn số sao trung bình từ bảng ratings chỉ với các đánh giá đã được phê duyệt
$rating_sql = "SELECT AVG(r.rating) as avg_rating 
               FROM ratings r 
               WHERE r.SachID = ? AND r.status = 'Approved'";
$rating_stmt = $conn->prepare($rating_sql);
$rating_stmt->bind_param("i", $sachID);
$rating_stmt->execute();
$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();
$average_rating = round($rating_data['avg_rating'], 1);

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sách</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f0f8ff;
            color: #333;
        }

        .container {
            margin-top: 40px;
            margin-bottom: 40px;
            max-width: 85%;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border-left: 5px solid #20c997;
            /* Màu xanh ngọc */
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            background-color: #20c997;
            /* Màu xanh ngọc */
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }

        .book-image img {
            max-width: 100%;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .book-info h2 {
            font-size: 30px;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #20c997;
            /* Màu xanh ngọc */
            padding-bottom: 10px;
        }

        .book-info .book-price {
            font-size: 24px;
            color: #fd7e14;
            /* Màu cam */
            font-weight: bold;
            margin-bottom: 20px;
        }

        .book-info .book-quantity {
            font-size: 18px;
            color: #d9534f;
            margin-bottom: 20px;
        }

        .btn-custom {
            background-color: #20c997;
            /* Màu xanh ngọc */
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #17a2b8;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Chi tiết Sách</h1>
        </div>
        <div class="row details">
            <div class="col-md-4 book-image text-center">
                <img src="<?php echo htmlspecialchars($sach['HinhAnh']); ?>" alt="<?php echo htmlspecialchars($sach['TenSach']); ?>">
            </div>
            <div class="col-md-8 book-info">
                <h2><?php echo htmlspecialchars($sach['TenSach']); ?></h2>
                <p class="book-price"><strong>Giá:</strong> <?php echo number_format($sach['GiaBan']); ?> VND</p>
                <p class="book-quantity"><strong>Số lượng còn lại:</strong> <?php echo $sach['SoLuong']; ?></p>
                <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($sach['MoTa']); ?></p>
                <p><strong>Số trang:</strong> <?php echo $sach['SoTrang']; ?></p>
                <p><strong>Thể loại:</strong> <?php echo htmlspecialchars($sach['TheLoai']); ?></p>
                <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($sach['TacGia']); ?></p>
                <p><strong>Dịch giả:</strong> <?php echo htmlspecialchars($sach['DichGia']); ?></p>
                <p><strong>Nhà xuất bản:</strong> <?php echo htmlspecialchars($sach['TenNXB']); ?></p>
                <p><strong>Kích thước:</strong> <?php echo htmlspecialchars($sach['KichThuoc']); ?></p>
                <p><strong>Khối lượng:</strong> <?php echo htmlspecialchars($sach['KhoiLuong']); ?> kg</p>
                <p><strong>Đánh giá:</strong> <?php echo $average_rating; ?> Sao</p>

                <!-- Form Thêm vào giỏ -->
                <form id="add-to-cart-form" method="post" action="add_to_cart.php" class="d-inline">
                    <input type="hidden" name="sachID" value="<?php echo $sach['SachID']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="button" class="btn btn-custom" id="add-to-cart-btn">Thêm vào giỏ</button>
                </form>

                <!-- Form Mua ngay -->
                <form id="buy-now-form" action="checkout.php" method="post" class="d-inline">
                    <input type="hidden" name="sachID" value="<?php echo $sach['SachID']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="buyNow" value="true">
                    <button type="submit" class="btn btn-custom">Mua ngay</button>
                </form>
                <a href="index.php" class="btn btn-custom home-button">Quay lại trang chủ</a>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#add-to-cart-btn').click(function() {
                var formData = $('#add-to-cart-form').serialize(); // Lấy dữ liệu từ form

                $.ajax({
                    type: "POST",
                    url: "add_to_cart.php",
                    data: formData,
                    success: function(response) {
                        alert(response);
                    },
                    error: function() {
                        alert("Có lỗi xảy ra, vui lòng thử lại.");
                    }
                });
            });
        });
    </script>
</body>

</html>