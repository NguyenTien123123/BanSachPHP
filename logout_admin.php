<?php
session_start();
session_destroy(); // Hủy tất cả các session
header("Location: admin_login.php"); // Chuyển người dùng về trang đăng nhập
exit;