<?php
require_once("./config.php");
session_start();

$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>VNPAY RESPONSE</title>
    <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet" />
    <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Style for the VNPAY response page */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h3 {
            color: #333;
            font-size: 26px;
            margin: 0;
            font-weight: 700;
        }

        .table-responsive {
            width: 100%;
            margin-bottom: 20px;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            border-radius: 5px;
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 10px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.3s;
        }

        .form-group:hover {
            background-color: #f8f9fa;
        }

        .form-group label {
            font-size: 16px;
            color: #555;
        }

        .form-group label:nth-child(2) {
            font-weight: 600;
            color: #0069d9;
        }

        .result-label {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .result-label.success {
            color: #28a745;
        }

        .result-label.failure {
            color: #dc3545;
        }

        .result-label.success i {
            color: #28a745;
        }

        .result-label.failure i {
            color: #dc3545;
        }

        footer.footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 30px;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 10px 10px;
        }

        footer.footer p {
            margin: 0;
            color: #6c757d;
        }

        footer.footer .btn {
            margin-top: 10px;
            background-color: #0069d9;
            border-color: #0069d9;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s, border-color 0.3s;
        }

        footer.footer .btn:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header clearfix">
            <h3 class="text-muted">VNPAY RESPONSE</h3>
        </div>
        <div class="table-responsive">
            <div class="form-group">
                <label>Mã đơn hàng:</label>
                <label><?php echo $_GET['vnp_TxnRef'] ?></label>
            </div>
            <div class="form-group">
                <label>Số tiền:</label>
                <label><?php echo number_format($_GET['vnp_Amount'] / 100, 0, ',', '.') ?> VND</label>
            </div>
            <div class="form-group">
                <label>Nội dung thanh toán:</label>
                <label><?php echo $_GET['vnp_OrderInfo'] ?></label>
            </div>
            <div class="form-group">
                <label>Mã phản hồi (vnp_ResponseCode):</label>
                <label><?php echo $_GET['vnp_ResponseCode'] ?></label>
            </div>
            <div class="form-group">
                <label>Mã GD Tại VNPAY:</label>
                <label><?php echo $_GET['vnp_TransactionNo'] ?></label>
            </div>
            <div class="form-group">
                <label>Mã Ngân hàng:</label>
                <label><?php echo $_GET['vnp_BankCode'] ?></label>
            </div>
            <div class="form-group">
                <label>Thời gian thanh toán:</label>
                <label><?php echo $_GET['vnp_PayDate'] ?></label>
            </div>
            <div class="form-group">
                <label>Kết quả:</label>
                <label class="result-label <?php echo ($secureHash == $vnp_SecureHash && $_GET['vnp_ResponseCode'] == '00') ? 'success' : 'failure' ?>">
                    <?php
                    if ($secureHash == $vnp_SecureHash) {
                        if ($_GET['vnp_ResponseCode'] == '00') {
                            echo "<i class='fas fa-check-circle'></i>GD Thành công";

                            // Kết nối đến database và cập nhật trạng thái đơn hàng
                            include 'db_connect.php';
                            $userID = $_SESSION['userid'];
                            $orderID = $_GET['vnp_TxnRef'];
                            $total = $_GET['vnp_Amount'] / 100; // Chuyển đổi tổng tiền từ VNPay phản hồi

                            // Bắt đầu giao dịch
                            $conn->begin_transaction();

                            try {
                                // Thêm đơn hàng
                                $sql = "INSERT INTO donhang (ID, NgayDatHang, TongTien, TrangThai, DiaChiGiaoHang, PhuongThucThanhToan) 
                                            VALUES (?, NOW(), ?, 'Completed', '', 'online')";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt) {
                                    throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
                                }
                                $stmt->bind_param("id", $userID, $total);
                                if (!$stmt->execute()) {
                                    throw new Exception("Lỗi thực thi câu lệnh: " . $stmt->error);
                                }
                                $orderID = $stmt->insert_id;

                                // Lấy thông tin từ giỏ hàng
                                $sql = "SELECT g.SachID, s.TenSach, s.GiaBan, g.SoLuong, s.SoLuong AS SoLuongTonKho, (s.GiaBan * g.SoLuong) AS Subtotal 
                                            FROM giohang g
                                            JOIN sach s ON g.SachID = s.SachID 
                                            WHERE g.ID = ?";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt) {
                                    throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
                                }
                                $stmt->bind_param("i", $userID);
                                if (!$stmt->execute()) {
                                    throw new Exception("Lỗi thực thi câu lệnh: " . $stmt->error);
                                }
                                $result = $stmt->get_result();
                                $items = [];
                                while ($row = $result->fetch_assoc()) {
                                    if ($row['SoLuongTonKho'] >= $row['SoLuong']) {
                                        $items[] = $row;
                                    }
                                }

                                // Thêm chi tiết đơn hàng và cập nhật số lượng sách
                                foreach ($items as $item) {
                                    $sql = "INSERT INTO chitietdonhang (DHID, SachID, SoLuong, DonGia) 
                                                VALUES (?, ?, ?, ?)";
                                    $stmt = $conn->prepare($sql);
                                    if (!$stmt) {
                                        throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
                                    }
                                    $stmt->bind_param("iiid", $orderID, $item['SachID'], $item['SoLuong'], $item['GiaBan']);
                                    if (!$stmt->execute()) {
                                        throw new Exception("Lỗi thực thi câu lệnh: " . $stmt->error);
                                    }

                                    // Cập nhật số lượng tồn kho
                                    $sql = "UPDATE sach SET SoLuong = SoLuong - ? WHERE SachID = ?";
                                    $stmt = $conn->prepare($sql);
                                    if (!$stmt) {
                                        throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
                                    }
                                    $stmt->bind_param("ii", $item['SoLuong'], $item['SachID']);
                                    if (!$stmt->execute()) {
                                        throw new Exception("Lỗi thực thi câu lệnh: " . $stmt->error);
                                    }
                                }

                                // Xóa giỏ hàng
                                $sql = "DELETE FROM giohang WHERE ID = ?";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt) {
                                    throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
                                }
                                $stmt->bind_param("i", $userID);
                                if (!$stmt->execute()) {
                                    throw new Exception("Lỗi thực thi câu lệnh: " . $stmt->error);
                                }

                                // Thêm thông báo cho người dùng
                                $sql = "INSERT INTO ThongBao (UserID, NoiDung) VALUES (?, 'Đơn hàng của bạn đã được thanh toán thành công')";
                                $stmt = $conn->prepare($sql);
                                if (!$stmt) {
                                    throw new Exception("Lỗi chuẩn bị câu lệnh: " . $conn->error);
                                }
                                $stmt->bind_param("i", $userID);
                                if (!$stmt->execute()) {
                                    throw new Exception("Lỗi thực thi câu lệnh: " . $stmt->error);
                                }

                                // Commit giao dịch
                                $conn->commit();
                            } catch (Exception $e) {
                                // Rollback giao dịch nếu có lỗi
                                $conn->rollback();
                                echo "<span style='color:red'>Có lỗi xảy ra trong quá trình xử lý đơn hàng: " . $e->getMessage() . "</span>";
                            }

                            // Đóng kết nối
                            $stmt->close();
                            $conn->close();
                        } else {
                            echo "<i class='fas fa-times-circle'></i>GD Không thành công";
                        }
                    } else {
                        echo "<i class='fas fa-times-circle'></i>Chữ ký không hợp lệ";
                    }
                    ?>
                </label>
            </div>
        </div>
        <p>&nbsp;</p>
        <footer class="footer">
            <p>&copy; VNPAY <?php echo date('Y') ?></p>
            <a href="index.php" class="btn">Quay lại trang chủ</a>
        </footer>
    </div>
</body>

</html>
l