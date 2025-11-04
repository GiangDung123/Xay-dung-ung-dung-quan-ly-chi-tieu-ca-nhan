<?php
session_start();

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['username']);

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$current_date = date("d/m/Y");
$current_money = 0;
$income = 0;
$expense = 0;
$income_list = [];
$expense_list = [];

// Nếu người dùng đã đăng nhập, lấy dữ liệu
if ($isLoggedIn) {
    $user = $_SESSION['username'];
    $query = mysqli_query($conn, "SELECT id FROM users WHERE username='$user'");
    $row = mysqli_fetch_assoc($query);
    $user_id = $row['id'];

    // Tính tổng tiền hiện có
    $income = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM incomes WHERE user_id='$user_id'"))['total'] ?? 0;
    $expense = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM expenses WHERE user_id='$user_id'"))['total'] ?? 0;
    $current_money = $income - $expense;

    // Lấy 5 khoản thu nhập gần nhất
    $income_query = mysqli_query($conn, "SELECT title, amount, date FROM incomes WHERE user_id='$user_id' ORDER BY date DESC LIMIT 5");
    while ($r = mysqli_fetch_assoc($income_query)) {
        $income_list[] = $r;
    }

    // Lấy 5 khoản chi tiêu gần nhất
    $expense_query = mysqli_query($conn, "SELECT title, amount, date FROM expenses WHERE user_id='$user_id' ORDER BY date DESC LIMIT 5");
    while ($r = mysqli_fetch_assoc($expense_query)) {
        $expense_list[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Trang chủ - Quản lý chi tiêu</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: linear-gradient(135deg, #f8efba, #fad390);
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 900px;
        margin: 50px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        padding: 30px;
        text-align: center;
        position: relative;
    }
    .top-right {
        position: absolute;
        top: 20px;
        right: 40px;
        text-align: right;
    }
    .top-right a {
        text-decoration: none;
        color: #d63031;
        font-weight: bold;
        display: block;
        margin: 4px 0;
    }
    .top-right a:hover {
        text-decoration: underline;
    }
    h2 {
        color: #d35400;
    }
    .box {
        background: #fff5e1;
        padding: 20px;
        border-radius: 10px;
        margin: 20px auto;
        width: 80%;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }
    .history {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        margin-top: 20px;
        text-align: left;
    }
    .history-box {
        width: 40%;
        background: #fef4f4;
        border: 1px solid #ffd3d3;
        border-radius: 12px;
        padding: 15px;
        font-weight: 500;
        color: #333;
    }
    .history-box h3 {
        text-align: center;
        color: #c0392b;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 14px;
    }
    th, td {
        border-bottom: 1px solid #eee;
        padding: 5px;
        text-align: left;
    }
    th {
        color: #555;
        font-weight: bold;
    }
    .btn-row {
        display: flex;
        justify-content: space-around;
        margin-top: 25px;
    }
    .btn {
        background: #e67e22;
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 25px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn:hover {
        background: #d35400;
    }
</style>
</head>
<body>

<div class="top-right">
    <?php if ($isLoggedIn): ?>
        <div>Xin chào, <b><?php echo $_SESSION['username']; ?></b></div>
        <a href="dangxuat.php">Đăng xuất</a>
    <?php else: ?>
        <a href="dangnhap.php">Đăng nhập</a>
        <a href="dangky.php">Đăng ký</a>
    <?php endif; ?>
</div>

<div class="container">
    <h2>💰 Quản lý thu chi cá nhân</h2>

    <div class="box">Số tiền hiện tại đang có: 
        <span style="color:#27ae60">
            <?php echo number_format($current_money, 0); ?> VNĐ
        </span>
    </div>

    <div class="box">Ngày hiện tại: <?php echo $current_date; ?></div>

    <!-- Biểu đồ tỷ lệ thu - chi -->
    <?php if ($isLoggedIn): ?>
        <canvas id="chartThuChi" width="350" height="200" style="margin: 20px auto;"></canvas>
        <script>
        const ctx = document.getElementById('chartThuChi');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Thu nhập', 'Chi tiêu'],
                datasets: [{
                    data: [<?php echo $income; ?>, <?php echo $expense; ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c'],
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Tỷ lệ Thu nhập - Chi tiêu',
                        font: { size: 18 }
                    }
                }
            }
        });
        </script>
    <?php endif; ?>

    <div class="history">
        <div class="history-box">
            <h3>Lịch sử Thu nhập</h3>
            <table>
                <tr><th>Ngày</th><th>Tên</th><th>Số tiền</th></tr>
                <?php if ($isLoggedIn && count($income_list) > 0): ?>
                    <?php foreach ($income_list as $i): ?>
                        <tr>
                            <td><?php echo $i['date']; ?></td>
                            <td><?php echo htmlspecialchars($i['title']); ?></td>
                            <td><?php echo number_format($i['amount']); ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center;">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
            </table>
            <div style="text-align:center; margin-top:8px;">
                <a href="thunhap.php">Xem thêm →</a>
            </div>
        </div>

        <div class="history-box">
            <h3>Lịch sử Chi tiêu</h3>
            <table>
                <tr><th>Ngày</th><th>Tên</th><th>Số tiền</th></tr>
                <?php if ($isLoggedIn && count($expense_list) > 0): ?>
                    <?php foreach ($expense_list as $e): ?>
                        <tr>
                            <td><?php echo $e['date']; ?></td>
                            <td><?php echo htmlspecialchars($e['title']); ?></td>
                            <td>-<?php echo number_format($e['amount']); ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center;">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
            </table>
            <div style="text-align:center; margin-top:8px;">
                <a href="chitieu.php">Xem thêm →</a>
            </div>
        </div>
    </div>

    <div class="btn-row">
        <button class="btn" onclick="window.location='thongke.php'">Thống kê</button>
    </div>
</div>

</body>
</html>
