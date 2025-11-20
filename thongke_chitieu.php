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

// ===================================
// BỔ SUNG LỌC TỪ NGÀY ĐẾN NGÀY
// ===================================
// Khởi tạo mệnh đề WHERE cơ bản (sử dụng alias 'e' cho bảng expenses)
$filter_clause = "WHERE e.user_id='$user_id'";
$from_date = '';
$to_date = '';
$display_range = "";

if (isset($_POST['filter_date']) && !empty($_POST['from_date']) && !empty($_POST['to_date'])) {
    // Lọc theo ngày (Sử dụng mysqli_real_escape_string để an toàn hơn)
    // Lưu ý: Trong môi trường production, nên sử dụng Prepared Statements.
    $from_date = mysqli_real_escape_string($conn, $_POST['from_date']);
    $to_date = mysqli_real_escape_string($conn, $_POST['to_date']);
    $filter_clause .= " AND e.date BETWEEN '$from_date' AND '$to_date'";
    
    // Chuỗi hiển thị cho tiêu đề
    $display_range = " (Từ: " . date('d/m/Y', strtotime($from_date)) . " - Đến: " . date('d/m/Y', strtotime($to_date)) . ")";
}


// 1. Tổng chi tiêu
// Cần thêm alias 'e' cho bảng expenses
$totalQuery = mysqli_query($conn, 
    "SELECT SUM(e.amount) AS total FROM expenses e $filter_clause");
$total = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;


// 2. Chi tiêu theo tháng
$monthQuery = mysqli_query($conn,
    "SELECT MONTH(e.date) AS thang, SUM(e.amount) AS tong 
     FROM expenses e
     $filter_clause
     GROUP BY MONTH(e.date)");

$monthData = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($monthQuery)) {
    $monthData[$row['thang']] = $row['tong'];
}


// 3. Chi tiêu theo danh mục
$catQuery = mysqli_query($conn,
    "SELECT c.name, SUM(e.amount) AS tong
     FROM expenses e
     LEFT JOIN categories c ON e.category_id = c.id
     $filter_clause
     GROUP BY c.name");

$catLabels = [];
$catValues = [];
while ($r = mysqli_fetch_assoc($catQuery)) {
    $catLabels[] = $r['name'] ?? "(Không có)";
    $catValues[] = $r['tong'];
}


// 4. Chi tiêu theo ngày (7 ngày gần nhất hoặc trong khoảng thời gian lọc)
$weeklyDataQuery = 
    "SELECT e.date, SUM(e.amount) AS tong
     FROM expenses e
     $filter_clause
     GROUP BY e.date
     ORDER BY e.date ASC";

// Nếu KHÔNG có lọc ngày, ta vẫn dùng logic cũ: 7 ngày chi tiêu gần nhất
if (empty($from_date) || empty($to_date)) {
    $weeklyDataQuery = 
        "SELECT e.date, SUM(e.amount) AS tong
         FROM expenses e
         WHERE e.user_id = '$user_id'
         GROUP BY e.date
         ORDER BY e.date DESC
         LIMIT 7";
}


$weeklyData = mysqli_query($conn, $weeklyDataQuery);

$week_labels = [];
$week_values = [];

while ($r = mysqli_fetch_assoc($weeklyData)) {
    $week_labels[] = $r['date'];
    $week_values[] = $r['tong'];
}

// Nếu là query LIMIT 7 (tức là không lọc ngày), ta phải đảo ngược lại mảng để hiển thị từ ngày cũ đến ngày mới.
if (empty($from_date) || empty($to_date)) {
    $week_labels = array_reverse($week_labels);
    $week_values = array_reverse($week_values);
}

// ===================================
// End of PHP logic
// ===================================
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
    <h2>THỐNG KÊ CHI TIÊU <?php echo $display_range; ?></h2>
    
    <form method="POST" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #eee; border-radius: 8px; display: flex; align-items: center; justify-content: flex-start; gap: 10px;">
        <label for="from_date" style="font-weight: bold;">Từ ngày:</label>
        <input type="date" id="from_date" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" required style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
        
        <label for="to_date" style="font-weight: bold;">Đến ngày:</label>
        <input type="date" id="to_date" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>" required style="padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
        
        <button type="submit" name="filter_date" style="padding: 8px 15px; background: #d63031; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Xem thống kê</button>
        
        <?php if (!empty($from_date)): // Chỉ hiển thị nút "Đặt lại" khi đang có filter ?>
            <a href="thongke_chitieu.php" style="padding: 8px 15px; text-decoration: none; color: #333; border: 1px solid #ccc; border-radius: 5px; display: inline-block; background: #fff;">Đặt lại</a>
        <?php endif; ?>
    </form>

    <div class="card">
        <b>Tổng chi tiêu:</b>
        <?php echo number_format($total, 0); ?> VNĐ
    </div>
    
    <h3>Biểu đồ chi tiêu theo ngày (<?php echo empty($from_date) ? '7 ngày gần nhất' : 'Trong khoảng lọc'; ?>)</h3>
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