<?php
$servername = "localhost";
$username   = "root";
$password   = ""; // Mặc định của WAMP không có mật khẩu
$database   = "quanlythuchi"; // Tên database bạn đã tạo trong phpMyAdmin

// Kết nối MySQLi (phiên bản hướng thủ tục)
$conn = mysqli_connect($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn) {
    echo "<script>alert('Kết nối thành công');</script>";
} else {
    echo "<script>alert('Kết nối thất bại: " . mysqli_connect_error() . "');</script>";
}
?>
