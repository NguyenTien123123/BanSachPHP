<?php
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Nếu sử dụng cookie để lưu trữ session, hãy xóa cookie đó
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

// Hủy session
session_destroy();

// Chuyển hướng về trang Index.php
header("Location: Index.php");
exit();
