<?php
include 'db_connect.php'; // Kết nối đến database

$orderID = isset($_GET['orderID']) ? $_GET['orderID'] : die('ERROR: Order ID not specified.');

// Truy vấn để lấy thông tin đơn hàng
$sql = "SELECT donhang.DHID, donhang.NgayDatHang, donhang.TongTien, donhang.TrangThai, donhang.DiaChiGiaoHang, donhang.PhuongThucThanhToan,
        nguoidung.TenDangNhap, nguoidung.Email, nguoidung.SoDienThoai, nguoidung.DiaChi
        FROM donhang
        JOIN nguoidung ON donhang.ID = nguoidung.ID
        WHERE donhang.DHID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderID);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "<p>Đơn hàng không tồn tại.</p>";
    exit;
}

// Truy vấn để lấy chi tiết các sản phẩm trong đơn hàng
$sql = "SELECT sach.TenSach, sach.TacGia, sach.TheLoai, chitietdonhang.SoLuong, chitietdonhang.DonGia
        FROM chitietdonhang
        JOIN sach ON chitietdonhang.SachID = sach.SachID
        WHERE chitietdonhang.DHID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderID);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: #007bff;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        .btn {
            margin: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Chi Tiết Đơn Hàng: <?php echo $order['DHID']; ?></h1>
        <p><strong>Ngày Đặt Hàng:</strong> <?php echo date("d-m-Y", strtotime($order['NgayDatHang'])); ?></p>
        <p><strong>Tổng Tiền:</strong> <?php echo number_format($order['TongTien'], 2); ?> VND</p>
        <p><strong>Trạng Thái:</strong> <?php echo $order['TrangThai']; ?></p>
        <p><strong>Địa Chỉ Giao Hàng:</strong> <?php echo $order['DiaChiGiaoHang']; ?></p>
        <p><strong>Phương Thức Thanh Toán:</strong> <?php echo $order['PhuongThucThanhToan']; ?></p>
        <h2>Thông Tin Người Đặt Hàng</h2>
        <p><strong>Tên Đăng Nhập:</strong> <?php echo $order['TenDangNhap']; ?></p>
        <p><strong>Email:</strong> <?php echo $order['Email']; ?></p>
        <p><strong>Số Điện Thoại:</strong> <?php echo $order['SoDienThoai']; ?></p>
        <p><strong>Địa Chỉ:</strong> <?php echo $order['DiaChi']; ?></p>

        <h2>Sản phẩm trong đơn:</h2>
        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Tên Sách</th>
                        <th>Tác Giả</th>
                        <th>Thể Loại</th>
                        <th>Số Lượng</th>
                        <th>Đơn Giá</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $product['TenSach']; ?></td>
                            <td><?php echo $product['TacGia']; ?></td>
                            <td><?php echo $product['TheLoai']; ?></td>
                            <td><?php echo $product['SoLuong']; ?></td>
                            <td><?php echo number_format($product['DonGia'], 2); ?> VND</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <a href="admin_orders.php" class="btn btn-primary">Quay lại danh sách đơn hàng</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>