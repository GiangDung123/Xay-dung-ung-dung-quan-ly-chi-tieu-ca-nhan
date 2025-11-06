<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: dangnhap.php");
    exit;
}

// 1. Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// 2. Truy vấn thống kê tổng quát
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$total_categories = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories"))['total'] ?? 0;

$income_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total, SUM(amount) AS total_money FROM incomes"));
$total_incomes = $income_data['total'] ?? 0;
$total_income_money = $income_data['total_money'] ?? 0;

$expense_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total, SUM(amount) AS total_money FROM expenses"));
$total_expenses = $expense_data['total'] ?? 0;
$total_expense_money = $expense_data['total_money'] ?? 0;

$total_balance = $total_income_money - $total_expense_money;

// 3. Lấy thống kê thu – chi theo tháng
$chart_data = [];
$sql_chart = "
    SELECT 
        MONTH(date) AS month,
        SUM(CASE WHEN 'incomes' THEN amount ELSE 0 END) AS income,
        0 AS expense
    FROM incomes
    GROUP BY MONTH(date)
    UNION ALL
    SELECT 
        MONTH(date) AS month,
        0 AS income,
        SUM(amount) AS expense
    FROM expenses
    GROUP BY MONTH(date)
";
$result_chart = mysqli_query($conn, $sql_chart);
$monthly = [];

while ($row = mysqli_fetch_assoc($result_chart)) {
    $month = $row['month'];
    if (!isset($monthly[$month])) {
        $monthly[$month] = ['income' => 0, 'expense' => 0];
    }
    $monthly[$month]['income'] += $row['income'];
    $monthly[$month]['expense'] += $row['expense'];
}

$months = [];
$income_values = [];
$expense_values = [];
foreach ($monthly as $m => $v) {
    $months[] = "Tháng " . $m;
    $income_values[] = $v['income'];
    $expense_values[] = $v['expense'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thống kê hệ thống - Admin</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #fef9e7, #fdebd0);
    margin: 0;
    padding: 0;
}
.container {
    max-width: 1000px;
    margin: 40px auto;
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
h2 {
    color: #d35400;
    text-align: center;
}
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 15px;
    margin-top: 25px;
}
.card {
    background: #fff5e1;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    text-align: center;
    font-weight: bold;
}
.card span {
    display: block;
    font-size: 22px;
    color: #2c3e50;
    margin-top: 10px;
}
.chart-container {
    margin-top: 40px;
}
.back {
    text-align: center;
    margin-top: 25px;
}
.back a {
    background: #d35400;
    color: white;
    padding: 10px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
}
.back a:hover {
    background: #e67e22;
}
</style>
</head>
<body>
<div class="container">
    <h2>📊 Thống kê hệ thống</h2>

    <div class="stats">
        <div class="card">👥 Người dùng<span><?php echo $total_users; ?></span></div>
        <div class="card">📂 Danh mục<span><?php echo $total_categories; ?></span></div>
        <div class="card">💰 Khoản thu<span><?php echo $total_incomes . " (" . number_format($total_income_money) . " VNĐ)"; ?></span></div>
        <div class="card">💸 Khoản chi<span><?php echo $total_expenses . " (" . number_format($total_expense_money) . " VNĐ)"; ?></span></div>
        <div class="card">💵 Số dư hệ thống<span style="color:#27ae60;"><?php echo number_format($total_balance); ?> VNĐ</span></div>
    </div>

    <div class="chart-container">
        <canvas id="chartThuChi"></canvas>
    </div>

    <div class="back">
        <a href="admin.php">← Quay lại trang quản trị</a>
    </div>
</div>

<script>
const ctx = document.getElementById('chartThuChi');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [
            {
                label: 'Thu nhập',
                data: <?php echo json_encode($income_values); ?>,
                backgroundColor: 'rgba(46, 204, 113, 0.7)'
            },
            {
                label: 'Chi tiêu',
                data: <?php echo json_encode($expense_values); ?>,
                backgroundColor: 'rgba(231, 76, 60, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: { display: true, text: 'Biểu đồ thu – chi theo tháng' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
