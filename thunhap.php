<?php
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập trước!'); window.location='dangnhap.php';</script>";
    exit;
}

// 2. Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$user = $_SESSION['username'];
$message = "";

// 3. Xử lý khi thêm thu nhập
if (isset($_POST['them'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $note = $_POST['note'];
    $date = $_POST['date'];

    // Lấy id người dùng
    $result = mysqli_query($conn, "SELECT id FROM users WHERE username='$user'");
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['id'];

    // Thêm dữ liệu vào bảng incomes
    $sql = "INSERT INTO incomes (user_id, title, amount, note, date) 
            VALUES ('$user_id', '$title', '$amount', '$note', '$date')";

    if (mysqli_query($conn, $sql)) {
        $message = "✅ Đã thêm thu nhập thành công!";
    } else {
        $message = "❌ Lỗi khi thêm: " . mysqli_error($conn);
    }
}

// 4. Lấy danh sách thu nhập của người dùng hiện tại
$result = mysqli_query($conn, 
    "SELECT title, amount, note, date FROM incomes 
     WHERE user_id = (SELECT id FROM users WHERE username='$user') 
     ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thu nhập</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: linear-gradient(135deg, #a8edea, #fed6e3);
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 850px;
        margin: 50px auto;
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        animation: fadeIn 0.6s ease;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-10px);}
        to {opacity: 1; transform: translateY(0);}
    }
    h2 {
        text-align: center;
        color: #0078ff;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }
    h3 {
        margin-top: 40px;
        color: #333;
        border-left: 5px solid #0078ff;
        padding-left: 10px;
    }
    form {
        margin-top: 20px;
        background: #f8faff;
        padding: 20px;
        border-radius: 10px;
    }
    label {
        font-weight: 600;
        color: #444;
    }
    input, textarea {
        width: 100%;
        padding: 10px;
        margin: 6px 0 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        transition: border-color 0.3s;
    }
    input:focus, textarea:focus {
        border-color: #0078ff;
        outline: none;
    }
    button {
        background: #0078ff;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 25px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0,120,255,0.2);
    }
    button:hover {
        background: #005fcc;
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,120,255,0.3);
    }
    .message {
        text-align: center;
        font-weight: bold;
        color: #27ae60;
        margin: 10px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 15px;
    }
    th, td {
        border: 1px solid #e0e0e0;
        padding: 10px;
        text-align: left;
    }
    th {
        background: #0078ff;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #eef6ff;
    }
    .back {
        text-align: center;
        margin-top: 25px;
    }
    .back a {
        color: #0078ff;
        text-decoration: none;
        font-weight: 600;
    }
    .back a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h2>💰 Quản lý Thu nhập</h2>
    <p style="text-align:center;">Xin chào, <b><?php echo $_SESSION['username']; ?></b> 👋</p>

    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Tên khoản thu:</label>
        <input type="text" name="title" placeholder="Ví dụ: Lương tháng 11" required>

        <label>Số tiền (VNĐ):</label>
        <input type="number" name="amount" step="0.01" placeholder="Nhập số tiền..." required>

        <label>Ghi chú:</label>
        <textarea name="note" rows="3" placeholder="Ví dụ: Lương công ty ABC..."></textarea>

        <label>Ngày thu:</label>
        <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>">

        <button type="submit" name="them">➕ Thêm thu nhập</button>
    </form>

    <h3>📋 Danh sách thu nhập</h3>
    <table>
        <tr>
            <th>Ngày</th>
            <th>Tên khoản thu</th>
            <th>Số tiền (VNĐ)</th>
            <th>Ghi chú</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['date']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td style="color:#27ae60; font-weight:bold;"><?php echo number_format($row['amount'], 0); ?></td>
            <td><?php echo htmlspecialchars($row['note']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="back">
        <a href="index.php">← Quay lại trang chính</a>
    </div>
</div>
</body>
</html>
