<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['userid'];
$address = $_POST['address'];
$payment = $_POST['payment'];
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

// Bắt đầu giao dịch
$conn->begin_transaction();

try {
    // Thêm đơn hàng
    $sql = "INSERT INTO donhang (ID, NgayDatHang, TongTien, TrangThai, DiaChiGiaoHang, PhuongThucThanhToan) 
            VALUES (?, NOW(), ?, 'Pending', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $userID, $total, $address, $payment);
    $stmt->execute();
    $orderID = $stmt->insert_id;

    // Thêm chi tiết đơn hàng và cập nhật số lượng sách
    foreach ($items as $item) {
        $sql = "INSERT INTO chitietdonhang (DHID, SachID, SoLuong, DonGia) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $orderID, $item['SachID'], $item['SoLuong'], $item['GiaBan']);
        $stmt->execute();

        // Cập nhật số lượng tồn kho
        $sql = "UPDATE sach SET SoLuong = SoLuong - ? WHERE SachID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $item['SoLuong'], $item['SachID']);
        $stmt->execute();

        // Kiểm tra nếu sách đã hết hàng sau khi cập nhật
        $sql = "SELECT SoLuong, TenSach FROM sach WHERE SachID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item['SachID']);
        $stmt->execute();
        $result = $stmt->get_result();
        $updated_sach = $result->fetch_assoc();

        if ($updated_sach['SoLuong'] == 0) {
            $_SESSION['out_of_stock'] = "Sách '{$updated_sach['TenSach']}' đã hết hàng.";
        }
    }

    // Xóa giỏ hàng
    $sql = "DELETE FROM giohang WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();

    // Thêm thông báo cho người dùng
    $sql = "INSERT INTO ThongBao (UserID, NoiDung) VALUES (?, 'Đơn hàng của bạn đã được đặt thành công')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();

    // Commit giao dịch
    $conn->commit();

    $_SESSION['success'] = "Đơn hàng của bạn đã được xử lý thành công!";
    header("Location: thank_you.php"); // Chuyển hướng tới trang cảm ơn
} catch (Exception $e) {
    // Rollback giao dịch nếu có lỗi
    $conn->rollback();
    $_SESSION['errors'] = ["Có lỗi xảy ra trong quá trình xử lý đơn hàng. Vui lòng thử lại."];
    header("Location: cart.php");
}

$conn->close();
