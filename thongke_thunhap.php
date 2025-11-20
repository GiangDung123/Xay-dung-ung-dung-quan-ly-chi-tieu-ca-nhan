<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location='dangnhap.php';</script>";
    exit;
}

// Kết nối DB
$conn = mysqli_connect("localhost", "root", "", "Quanlythuchi");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$user = $_SESSION['username'];
$userQuery = mysqli_query($conn, "SELECT id FROM users WHERE username='$user'");
$userData = mysqli_fetch_assoc($userQuery);
$user_id = $userData['id'];

// =============================
// NHẬN BỘ LỌC NGÀY
// =============================
$from = $_GET['from'] ?? "";
$to   = $_GET['to'] ?? "";
$where = "WHERE user_id='$user_id'";

if ($from !== "" && $to !== "") {
    $where .= " AND date BETWEEN '$from' AND '$to'";
}

// 1. Tổng thu nhập theo bộ lọc
$totalQuery = mysqli_query($conn, 
    "SELECT SUM(amount) AS total FROM incomes $where");
$total = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

// 2. Thu nhập theo tháng (12 tháng)
$monthQuery = mysqli_query($conn,
    "SELECT MONTH(date) AS thang, SUM(amount) AS tong 
     FROM incomes 
     WHERE user_id='$user_id'
     GROUP BY MONTH(date)");

$monthData = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($monthQuery)) {
    $monthData[$row['thang']] = $row['tong'];
}

// 3. Thu nhập theo danh mục
$catQuery = mysqli_query($conn,
    "SELECT c.name, SUM(i.amount) AS tong
     FROM incomes i
     LEFT JOIN categories c ON i.category_id = c.id
     WHERE i.user_id='$user_id'
     GROUP BY c.name");

$catLabels = [];
$catValues = [];
while ($r = mysqli_fetch_assoc($catQuery)) {
    $catLabels[] = $r['name'];
    $catValues[] = $r['tong'];
}

// 4. Thu nhập theo tuần (WEEK)
$weeklyQuery = mysqli_query($conn,
    "SELECT YEARWEEK(date,1) AS week, SUM(amount) AS tong
     FROM incomes
     WHERE user_id='$user_id'
     GROUP BY YEARWEEK(date,1)
     ORDER BY YEARWEEK(date,1)");

$week_labels = [];
$week_values = [];

while ($r = mysqli_fetch_assoc($weeklyQuery)) {
    $week_labels[] = "Tuần " . substr($r['week'], 4) . " / " . substr($r['week'], 0, 4);
    $week_values[] = $r['tong'];
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thống kê thu nhập</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        font-family: Segoe UI, sans-serif;
        background: #eef2f7;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 900px;
        margin: 40px auto;
        background: white;
        padding: 30px;
        border-radius: 14px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #0078ff;
        margin-bottom: 20px;
    }
    .card {
        padding: 20px;
        background: #f7fbff;
        border-left: 5px solid #0078ff;
        margin-bottom: 20px;
        border-radius: 10px;
        font-size: 18px;
    }
    .filter-box {
        background: #f0f6ff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    label { font-weight: bold; }
    input[type="date"] {
        padding: 6px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }
    button {
        padding: 8px 12px;
        background: #0078ff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .back {
        text-align: center;
        margin-top: 20px;
    }
    .back a {
        text-decoration: none;
        color: #0078ff;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="container">
    <h2>THỐNG KÊ THU NHẬP</h2>

    <!-- FILTER -->
    <div class="filter-box">
        <form method="GET">
            <label>Từ ngày:</label>
            <input type="date" name="from" value="<?php echo $from; ?>">

            <label style="margin-left:20px">Đến ngày:</label>
            <input type="date" name="to" value="<?php echo $to; ?>">

            <button type="submit">Lọc</button>
            <a href="thongke_thunhap.php" style="margin-left:10px; color:red; font-weight:bold;">Reset</a>
        </form>
    </div>

    <div class="card">
        <b>Tổng thu nhập:</b> 
        <?php echo number_format($total, 0); ?> VNĐ
    </div>

    <!-- WEEKLY CHART -->
    <h3>Biểu đồ thu nhập theo tuần</h3>
    <canvas id="weekChart"></canvas>

    <h3>Biểu đồ thu nhập theo tháng</h3>
    <canvas id="chartMonth"></canvas>

    <h3>Biểu đồ thu nhập theo danh mục</h3>
    <canvas id="chartCategory"></canvas>

    <div class="back"><a href="index.php">← Quay lại trang chính</a></div>
</div>

<script>
// --- Biểu đồ theo tuần ---
new Chart(document.getElementById("weekChart"), {
    type: "line",
    data: {
        labels: <?php echo json_encode($week_labels); ?>,
        datasets: [{
            label: "Thu nhập theo tuần",
            data: <?php echo json_encode($week_values); ?>,
            borderWidth: 2,
            borderColor: "#0078ff",
            backgroundColor: "rgba(0,120,255,0.1)",
            tension: 0.2
        }]
    }
});

// --- Biểu đồ thu nhập theo tháng ---
new Chart(document.getElementById('chartMonth'), {
    type: 'bar',
    data: {
        labels: ['1','2','3','4','5','6','7','8','9','10','11','12'],
        datasets: [{
            label: 'Thu nhập (VNĐ)',
            data: <?php echo json_encode(array_values($monthData)); ?>,
            backgroundColor: 'rgba(0,120,255,0.6)',
            borderColor: '#0078ff',
            borderWidth: 1
        }]
    }
});

// --- Biểu đồ theo danh mục ---
new Chart(document.getElementById('chartCategory'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($catLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($catValues); ?>,
            backgroundColor: ['#0078ff','#ff7675','#55efc4','#ffeaa7','#6c5ce7']
        }]
    }
});
</script>

</body>
</html>
