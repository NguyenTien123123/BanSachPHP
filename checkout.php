<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['userid'];
$total = 0;
$items = [];
$errors = [];

// Kiểm tra xem có phải từ nút "Mua ngay" không
if (isset($_POST['buyNow']) && $_POST['buyNow'] == 'true') {
    $sachID = $_POST['sachID'];
    $quantity = $_POST['quantity'];

    // Truy vấn thông tin sách cụ thể để mua ngay
    $sql = "SELECT TenSach, GiaBan, SoLuong FROM sach WHERE SachID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sachID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['SoLuong'] >= $quantity) {
            $items[] = [
                'SachID' => $sachID,
                'TenSach' => $row['TenSach'],
                'SoLuong' => $quantity,
                'GiaBan' => $row['GiaBan'],
                'Subtotal' => $row['GiaBan'] * $quantity
            ];
            $total += $row['GiaBan'] * $quantity;
        } else {
            $errors[] = "Sách '{$row['TenSach']}' không đủ số lượng. Chỉ còn {$row['SoLuong']} cuốn trong kho.";
        }
    } else {
        $errors[] = "Sách không tồn tại.";
    }
    $stmt->close();
} else {
    // Nếu không phải mua ngay, lấy thông tin từ giỏ hàng
    $sql = "SELECT g.SachID, s.TenSach, s.GiaBan, g.SoLuong, s.SoLuong AS SoLuongTonKho, (s.GiaBan * g.SoLuong) AS Subtotal 
            FROM giohang g
            JOIN sach s ON g.SachID = s.SachID 
            WHERE g.ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['SoLuongTonKho'] >= $row['SoLuong']) {
            $items[] = $row;
            $total += $row['Subtotal'];
        } else {
            $errors[] = "Sách '{$row['TenSach']}' không đủ số lượng. Chỉ còn {$row['SoLuongTonKho']} cuốn trong kho.";
        }
    }
    $stmt->close();
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: cart.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .checkout-container {
            max-width: 800px;
            background: #fff;
            margin: 30px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            border-left: 5px solid #20c997;
        }

        h1 {
            color: #20c997;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
        }

        button {
            width: 100%;
            padding: 10px;
            color: #fff;
            background-color: #fd7e14;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        button:hover {
            background-color: #e36d0a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .cart-summary {
            text-align: right;
            margin-top: 20px;
        }

        .cart-summary p {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container checkout-container">
        <h1>Thanh Toán</h1>
        <form id="checkout-form" action="process_checkout.php" method="post">
            <div class="form-group">
                <label for="address">Địa chỉ giao hàng:</label>
                <input type="text" id="address" name="address" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="payment">Phương thức thanh toán:</label>
                <select id="payment" name="payment" class="form-control">
                    <option value="cod">Thanh toán khi nhận hàng</option>
                    <option value="online">Thanh toán trực tuyến</option>
                </select>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) {
                        echo "<tr><td>{$item['TenSach']}</td><td>{$item['SoLuong']}</td><td>" . number_format($item['GiaBan'], 2) . " VND</td><td>" . number_format($item['Subtotal'], 2) . " VND</td></tr>";
                    } ?>
                    <tr>
                        <td colspan="3"><strong>Tổng cộng</strong></td>
                        <td><strong><?php echo number_format($total, 2); ?> VND</strong></td>
                    </tr>
                </tbody>
            </table>
            <button type="submit">Xác nhận thanh toán</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('checkout-form').onsubmit = function(e) {
            const paymentMethod = document.getElementById('payment').value;
            if (paymentMethod === 'online') {
                if (confirm('Bạn sẽ được chuyển hướng đến trang thanh toán trực tuyến. Bạn có muốn tiếp tục?')) {
                    window.location.href = 'vnpay_create_payment.php?amount=<?php echo $total; ?>&order_id=<?php echo time(); ?>&order_desc=Thanh toan don hang';
                }
                e.preventDefault(); // Ngăn không cho form submit để chuyển hướng tới trang thanh toán trực tuyến
            } else {
                if (!confirm('Bạn có chắc chắn muốn tiếp tục thanh toán?')) {
                    e.preventDefault(); // Ngăn không cho form submit nếu người dùng không xác nhận
                }
            }
        };
    </script>

</body>

</html>