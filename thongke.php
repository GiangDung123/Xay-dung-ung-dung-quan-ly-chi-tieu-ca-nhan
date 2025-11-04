<?php
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập trước!'); window.location='dangnhap.php';</script>";
    exit;
}

// 2. Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$user = $_SESSION['username'];

// 3. Lấy user_id
$get_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$user'");
$user_row = mysqli_fetch_assoc($get_user);
$user_id = $user_row['id'];

// 4. Tính tổng thu nhập
$sql_income = "SELECT SUM(amount) AS total_income FROM incomes WHERE user_id='$user_id'";
$result_income = mysqli_query($conn, $sql_income);
$total_income = mysqli_fetch_assoc($result_income)['total_income'] ?? 0;

// 5. Tính tổng chi tiêu
$sql_expense = "SELECT SUM(amount) AS total_expense FROM expenses WHERE user_id='$user_id'";
$result_expense = mysqli_query($conn, $sql_expense);
$total_expense = mysqli_fetch_assoc($result_expense)['total_expense'] ?? 0;

// 6. Tính số dư
$balance = $total_income - $total_expense;

// 7. Lấy danh sách thu nhập & chi tiêu chi tiết
$list_income = mysqli_query($conn, "SELECT title, amount, date, note FROM incomes WHERE user_id='$user_id' ORDER BY date DESC");
$list_expense = mysqli_query($conn, "SELECT title, amount, date, note FROM expenses WHERE user_id='$user_id' ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thống kê Thu Chi</title>
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
        max-width: 1000px;
        margin: 40px auto;
        background: #fff;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .container:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    h2 {
        text-align: center;
        color: #2a5dca;
        font-size: 26px;
        margin-bottom: 10px;
    }

    p {
        text-align: center;
        color: #444;
        font-size: 16px;
    }

    .summary {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        margin: 30px 0;
        gap: 15px;
    }

    .box {
        flex: 1 1 30%;
        background: #f9fbff;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 1px solid #d9e3ff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .box:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .income h3 { color: #00a76f; }
    .expense h3 { color: #d32f2f; }
    .balance h3 { color: #1a73e8; }

    .box p {
        font-size: 20px;
        font-weight: bold;
        margin-top: 8px;
    }

    h3 {
        color: #333;
        border-left: 5px solid #2a5dca;
        padding-left: 10px;
        margin-top: 25px;
        font-size: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
    }

    th, td {
        border: none;
        padding: 12px;
        text-align: left;
    }

    th {
        background: #2a5dca;
        color: white;
        text-transform: uppercase;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    tr:nth-child(even) {
        background: #f4f7ff;
    }

    tr:hover {
        background: #e8efff;
    }

    td {
        font-size: 15px;
        color: #333;
    }

    .back {
        text-align: center;
        margin-top: 30px;
    }

    .back a {
        display: inline-block;
        background: #2a5dca;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    .back a:hover {
        background: #1a47a1;
    }
</style>

</head>
<body>
<div class="container">
    <h2>Thống kê Thu - Chi cá nhân</h2>
    <p style="text-align:center;">Xin chào, <b><?php echo $_SESSION['username']; ?></b>!</p>

    <div class="summary">
        <div class="box income">
            <h3>Tổng thu nhập</h3>
            <p><?php echo number_format($total_income, 0); ?> VNĐ</p>
        </div>
        <div class="box expense">
            <h3>Tổng chi tiêu</h3>
            <p>-<?php echo number_format($total_expense, 0); ?> VNĐ</p>
        </div>
        <div class="box balance">
            <h3>Số dư còn lại</h3>
            <p><?php echo number_format($balance, 0); ?> VNĐ</p>
        </div>
    </div>

    <h3>Chi tiết Thu nhập</h3>
    <table>
        <tr><th>Ngày</th><th>Tên khoản thu</th><th>Số tiền</th><th>Ghi chú</th></tr>
        <?php while ($row = mysqli_fetch_assoc($list_income)): ?>
        <tr>
            <td><?php echo $row['date']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo number_format($row['amount'], 0); ?> VNĐ</td>
            <td><?php echo htmlspecialchars($row['note']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h3>Chi tiết Chi tiêu</h3>
    <table>
        <tr><th>Ngày</th><th>Tên khoản chi</th><th>Số tiền</th><th>Ghi chú</th></tr>
        <?php while ($row = mysqli_fetch_assoc($list_expense)): ?>
        <tr>
            <td><?php echo $row['date']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td>-<?php echo number_format($row['amount'], 0); ?> VNĐ</td>
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
