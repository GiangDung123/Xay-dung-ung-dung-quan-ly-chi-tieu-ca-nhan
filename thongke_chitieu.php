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

// Lấy user_id
$userQuery = mysqli_query($conn, "SELECT id FROM users WHERE username='$user'");
$userData = mysqli_fetch_assoc($userQuery);
$user_id = $userData['id'];

// 1. Tổng chi tiêu
$totalQuery = mysqli_query($conn, 
    "SELECT SUM(amount) AS total FROM expenses WHERE user_id='$user_id'");
$total = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

// 2. Chi tiêu theo tháng
$monthQuery = mysqli_query($conn,
    "SELECT MONTH(date) AS thang, SUM(amount) AS tong 
     FROM expenses 
     WHERE user_id='$user_id'
     GROUP BY MONTH(date)");

$monthData = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($monthQuery)) {
    $monthData[$row['thang']] = $row['tong'];
}

// 3. Chi tiêu theo danh mục (nếu có bảng categories chi tiêu)
$catQuery = mysqli_query($conn,
    "SELECT c.name, SUM(e.amount) AS tong
     FROM expenses e
     LEFT JOIN categories c ON e.category_id = c.id
     WHERE e.user_id='$user_id'
     GROUP BY c.name");


$catLabels = [];
$catValues = [];
$weeklyData = mysqli_query($conn,
    "SELECT e.date, SUM(e.amount) AS tong
     FROM expenses e
     WHERE e.user_id = '$user_id'
     GROUP BY e.date
     ORDER BY e.date DESC
     LIMIT 7"
);

$week_labels = [];
$week_values = [];

while ($r = mysqli_fetch_assoc($weeklyData)) {
    $week_labels[] = $r['date'];
    $week_values[] = $r['tong'];
}

$week_labels = array_reverse($week_labels);
$week_values = array_reverse($week_values);
while ($r = mysqli_fetch_assoc($catQuery)) {
    $catLabels[] = $r['name'] ?? "(Không có)";
    $catValues[] = $r['tong'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thống kê chi tiêu</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        font-family: Segoe UI, sans-serif;
        background: #f3e7e9;
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
        color: #d63031;
        margin-bottom: 20px;
    }
    .card {
        padding: 20px;
        background: #fff4f4;
        border-left: 5px solid #d63031;
        margin-bottom: 20px;
        border-radius: 10px;
        font-size: 18px;
    }
    h3 {
        margin-top: 30px;
        color: #333;
    }
    canvas {
        margin-top: 20px;
    }
    .back {
        text-align: center;
        margin-top: 20px;
    }
    .back a {
        text-decoration: none;
        color: #d63031;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="container">
    <h2>THỐNG KÊ CHI TIÊU</h2>

    <div class="card">
        <b>Tổng chi tiêu:</b>
        <?php echo number_format($total, 0); ?> VNĐ
    </div>
        <h3>Biểu đồ chi tiêu 7 ngày gần nhất</h3>
    <canvas id="weekChart"></canvas>

    <script>
        new Chart(document.getElementById("weekChart"), {
            type: "line",
            data: {
                labels: <?php echo json_encode($week_labels); ?>,
                datasets: [{
                    label: "Chi tiêu theo ngày",
                    data: <?php echo json_encode($week_values); ?>,
                    borderWidth: 2,
                    borderColor: "red",
                    tension: 0.2
                }]
            }
        });
    </script>

    <h3>Biểu đồ chi tiêu theo tháng</h3>
    <canvas id="chartMonth"></canvas>

    <h3>Biểu đồ chi tiêu theo danh mục</h3>
    <canvas id="chartCategory"></canvas>

    <div class="back">
        <a href="index.php">← Quay lại trang chính</a>
    </div>
</div>

<script>
// Biểu đồ chi tiêu theo tháng
new Chart(document.getElementById('chartMonth'), {
    type: 'bar',
    data: {
        labels: ['1','2','3','4','5','6','7','8','9','10','11','12'],
        datasets: [{
            label: 'Chi tiêu (VNĐ)',
            data: <?php echo json_encode(array_values($monthData)); ?>,
            backgroundColor: 'rgba(214,48,49,0.6)',
            borderColor: '#d63031',
            borderWidth: 1
        }]
    }
});

// Biểu đồ theo danh mục
new Chart(document.getElementById('chartCategory'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($catLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($catValues); ?>,
            backgroundColor: ['#d63031','#ff7675','#fab1a0','#fdcb6e','#e17055','#6c5ce7']
        }]
    }
}); 
</script>

</body>
</html>
