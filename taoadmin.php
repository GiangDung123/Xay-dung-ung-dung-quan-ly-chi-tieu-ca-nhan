<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) die("Kết nối lỗi: " . mysqli_connect_error());

$hash = password_hash("123456", PASSWORD_DEFAULT);

// Xóa tài khoản admin cũ nếu có
mysqli_query($conn, "DELETE FROM users WHERE username='admin'");

// Thêm lại tài khoản admin đúng hash
$sql = "INSERT INTO users (username, password, fullname, role)
        VALUES ('admin', '$hash', 'Quản trị viên', 'admin')";

if (mysqli_query($conn, $sql)) {
    echo " Đã tạo tài khoản admin thành công!<br>";
    echo "Tên đăng nhập: admin<br>";
    echo "Mật khẩu: 123456<br>";
} else {
    echo " Lỗi: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
