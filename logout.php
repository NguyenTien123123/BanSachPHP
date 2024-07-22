<?php
session_start(); // Bắt đầu phiên làm việc

// Xóa tất cả biến session
$_SESSION = array();

// Nếu muốn hủy session, hủy cả cookie session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Cuối cùng, hủy bỏ phiên
session_destroy();

// Chuyển hướng về trang đăng nhập, hoặc trang chủ
header("Location: login.php");
exit;