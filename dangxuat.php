<?php
session_start();       // Bắt đầu session
session_unset();       // Xóa toàn bộ biến trong session
session_destroy();     // Hủy session hiện tại

// Quay lại trang đăng nhập
header("Location: dangnhap.php");
exit;
?>
