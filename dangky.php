<?php
// 1. Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi"; // đổi đúng tên database của bạn
$conn = mysqli_connect($servername, $username, $password, $database);

// 2. Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$message = "";

// 3. Khi người dùng bấm nút "Đăng ký"
if (isset($_POST['dangky'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $fullname = $_POST['fullname'];

    // Mã hóa mật khẩu để bảo mật
    $hashed = password_hash($pass, PASSWORD_DEFAULT);

    // Kiểm tra xem username có tồn tại chưa
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$user'");
    if (mysqli_num_rows($check) > 0) {
        $message = "Tên đăng nhập đã tồn tại!";
    } else {
        $sql = "INSERT INTO users (username, password, fullname) VALUES ('$user', '$hashed', '$fullname')";
        if (mysqli_query($conn, $sql)) {
            // Chuyển hướng về trang đăng nhập
            echo "<script>alert('Đăng ký thành công! Hãy đăng nhập.'); window.location='dangnhap.php';</script>";
            exit;
        } else {
            $message = "Lỗi khi đăng ký: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký tài khoản</title>
<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(135deg, #f0f7ff, #e8f0fe);
        margin: 0;
        padding: 0;
    }

    .container {
        width: 400px;
        background: #fff;
        margin: 80px auto;
        padding: 40px 30px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }

    h2 {
        text-align: center;
        color: #2a5dca;
        margin-bottom: 25px;
        font-size: 26px;
    }

    label {
        font-weight: 500;
        color: #333;
        display: block;
        margin-top: 10px;
        margin-bottom: 5px;
    }

    input[type=text], input[type=password] {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccd6f6;
        border-radius: 8px;
        font-size: 15px;
        background: #f9fbff;
        transition: all 0.2s ease;
    }

    input[type=text]:focus, input[type=password]:focus {
        outline: none;
        border-color: #2a5dca;
        background: #fff;
        box-shadow: 0 0 0 2px rgba(42,93,202,0.15);
    }

    button {
        width: 100%;
        padding: 12px;
        background: #2a5dca;
        color: white;
        border: none;
        border-radius: 8px;
        margin-top: 20px;
        font-size: 16px;
        cursor: pointer;
        font-weight: bold;
        letter-spacing: 0.5px;
        transition: background 0.3s ease, transform 0.1s ease;
    }

    button:hover {
        background: #1a47a1;
        transform: translateY(-2px);
    }

    .message {
        color: #d32f2f;
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }

    p {
        text-align: center;
        margin-top: 20px;
        font-size: 15px;
        color: #555;
    }

    a {
        color: #2a5dca;
        text-decoration: none;
        font-weight: 500;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Đăng ký tài khoản</h2>
    <form method="POST">
        <label>Tên đăng nhập:</label>
        <input type="text" name="username" required>

        <label>Mật khẩu:</label>
        <input type="password" name="password" required>

        <label>Họ và tên:</label>
        <input type="text" name="fullname">

        <button type="submit" name="dangky">Đăng ký</button>
    </form>
    <p class="message"><?php echo $message; ?></p>
    <p style="text-align:center;">Đã có tài khoản? <a href="dangnhap.php">Đăng nhập</a></p>
</div>
</body>
</html>
