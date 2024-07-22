<?php
require_once("./config.php");
$order_id = $_GET['order_id'];
$amount = $_GET['amount'];

$vnp_TxnRef = $order_id; // Sử dụng mã đơn hàng làm mã giao dịch tham chiếu của merchant
$vnp_Amount = $amount * 100; // Số tiền thanh toán
$vnp_Locale = 'vn'; // Ngôn ngữ chuyển hướng thanh toán
$vnp_BankCode = ''; // Mã phương thức thanh toán, để trống để chọn tại cổng thanh toán
$vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // IP Khách hàng thanh toán

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $expire
);

ksort($inputData);
$query = "";
$hashdata = "";
foreach ($inputData as $key => $value) {
    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$hashdata = ltrim($hashdata, '&');
$query = rtrim($query, '&');

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
}

header('Location: ' . $vnp_Url);
die();
