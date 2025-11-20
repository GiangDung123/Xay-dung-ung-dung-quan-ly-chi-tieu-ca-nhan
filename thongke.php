<?php
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập trước!'); window.location='dangnhap.php';</script>";
    exit;
}

// 2. Kết nối cơ sở dữ liệu
$conn = mysqli_connect("localhost", "root", "", "Quanlythuchi");
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$user = $_SESSION['username'];

// 3. Lấy user_id và làm sạch
$safe_user = mysqli_real_escape_string($conn, $user);
$get_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$safe_user'");
$user_row = mysqli_fetch_assoc($get_user);
$user_id = $user_row['id'];

// 4. Nhận và làm sạch bộ lọc ngày (Chống SQL Injection)
$from = $_GET['from'] ?? "";
$to   = $_GET['to'] ?? "";

$safe_user_id = mysqli_real_escape_string($conn, (string)$user_id); 
$safe_from = mysqli_real_escape_string($conn, $from);
$safe_to = mysqli_real_escape_string($conn, $to);

// Mệnh đề WHERE chung cho Thu nhập/Chi tiêu (bảng incomes/expenses)
$where_common = "WHERE user_id='$safe_user_id'";

// Mệnh đề WHERE cho truy vấn có JOIN (dùng alias)
$where_income_aliased = "WHERE i.user_id='$safe_user_id'";
$where_expense_aliased = "WHERE e.user_id='$safe_user_id'";

if ($safe_from !== "" && $safe_to !== "") {
    $where_common .= " AND date BETWEEN '$safe_from' AND '$safe_to'";
    $where_income_aliased .= " AND i.date BETWEEN '$safe_from' AND '$safe_to'";
    $where_expense_aliased .= " AND e.date BETWEEN '$safe_from' AND '$safe_to'";
}

// ========================================================
// A. TỔNG QUAN & DANH SÁCH CHI TIẾT (Áp dụng lọc ngày)
// ========================================================

// 1. Tổng thu nhập
$sql_total_income = "SELECT SUM(amount) AS total FROM incomes $where_common";
$total_income = mysqli_fetch_assoc(mysqli_query($conn, $sql_total_income))['total'] ?? 0;

// 2. Tổng chi tiêu
$sql_total_expense = "SELECT SUM(amount) AS total FROM expenses $where_common";
$total_expense = mysqli_fetch_assoc(mysqli_query($conn, $sql_total_expense))['total'] ?? 0;

// 3. Số dư
$balance = $total_income - $total_expense;

// 4. Chi tiết Thu nhập
$sql_list_income = "SELECT date, title, amount, note FROM incomes $where_common ORDER BY date DESC, id DESC";
$list_income = mysqli_query($conn, $sql_list_income);

// 5. Chi tiết Chi tiêu
$sql_list_expense = "SELECT date, title, amount, note FROM expenses $where_common ORDER BY date DESC, id DESC";
$list_expense = mysqli_query($conn, $sql_list_expense);


// ========================================================
// B. DỮ LIỆU BIỂU ĐỒ THEO THÁNG (Áp dụng lọc ngày)
// ========================================================

// 1. Thu nhập theo tháng
$monthIncomeQuery = mysqli_query($conn,
    "SELECT MONTH(date) AS thang, SUM(amount) AS tong 
     FROM incomes 
     $where_common 
     GROUP BY MONTH(date)");

$incomeMonthData = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($monthIncomeQuery)) {
    $incomeMonthData[$row['thang']] = $row['tong'];
}

// 2. Chi tiêu theo tháng
$monthExpenseQuery = mysqli_query($conn,
    "SELECT MONTH(date) AS thang, SUM(amount) AS tong 
     FROM expenses 
     $where_common 
     GROUP BY MONTH(date)");

$expenseMonthData = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($monthExpenseQuery)) {
    $expenseMonthData[$row['thang']] = $row['tong'];
}


// ========================================================
// C. DỮ LIỆU BIỂU ĐỒ THEO DANH MỤC (Áp dụng lọc ngày)
// ========================================================

// 1. Thu nhập theo danh mục
$catIncomeQuery = mysqli_query($conn,
    "SELECT c.name, SUM(i.amount) AS tong
     FROM incomes i
     LEFT JOIN categories c ON i.category_id = c.id
     $where_income_aliased 
     GROUP BY c.name");

$catIncomeLabels = [];
$catIncomeValues = [];
while ($r = mysqli_fetch_assoc($catIncomeQuery)) {
    $catIncomeLabels[] = $r['name'];
    $catIncomeValues[] = $r['tong'];
}

// 2. Chi tiêu theo danh mục
$catExpenseQuery = mysqli_query($conn,
    "SELECT c.name, SUM(e.amount) AS tong
     FROM expenses e
     LEFT JOIN categories c ON e.category_id = c.id
     $where_expense_aliased 
     GROUP BY c.name");

$catExpenseLabels = [];
$catExpenseValues = [];
while ($r = mysqli_fetch_assoc($catExpenseQuery)) {
    $catExpenseLabels[] = $r['name'];
    $catExpenseValues[] = $r['tong'];
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thống kê Tổng hợp</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body { font-family: Segoe UI, sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
    .container { width: 95%; max-width: 1200px; margin: 30px auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #0078ff; margin-bottom: 25px; }
    .filter-box { background: #f0f6ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; }
    label { font-weight: bold; }
    input[type="date"] { padding: 8px; border-radius: 6px; border: 1px solid #ccc; }
    button { padding: 8px 15px; background: #0078ff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;}
    button:hover { background: #005bb5; }
    a.reset-btn { color: #d63031; text-decoration: none; font-weight: bold; }
    .summary-cards { display: flex; justify-content: space-around; margin-bottom: 30px; gap: 20px; }
    .box { flex: 1; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .box h3 { margin-top: 0; font-size: 16px; }
    .box p { font-size: 24px; font-weight: bold; margin: 5px 0 0; }
    .income { background: #e6ffee; border-left: 5px solid #2ecc71; color: #2ecc71; }
    .expense { background: #ffe6e6; border-left: 5px solid #e74c3c; color: #e74c3c; }
    .balance { background: #f0f8ff; border-left: 5px solid #0078ff; color: #0078ff; }
    
    .charts-row { display: flex; gap: 20px; margin-bottom: 30px; }
    .chart-box { flex: 1; padding: 15px; border: 1px solid #ddd; border-radius: 8px; background: #fff; }
    
    h3 { color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 30px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table th, table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    table th { background-color: #f7f7f7; }
    .back { text-align: center; margin-top: 30px; }
    .back a { text-decoration: none; color: #0078ff; font-weight: bold; }
</style>
</head>
<body>

<div class="container">
    <h2>THỐNG KÊ TỔNG HỢP THU CHI</h2>

    <div class="filter-box">
        <form method="GET" style="display:flex; gap: 15px; align-items: center;">
            <label>Từ ngày:</label>
            <input type="date" name="from" value="<?php echo $from; ?>">

            <label>Đến ngày:</label>
            <input type="date" name="to" value="<?php echo $to; ?>">

            <button type="submit">Lọc</button>
            <a href="thongke.php" class="reset-btn">Reset</a>
        </form>
    </div>

    <div class="summary-cards">
        <div class="box income">
            <h3>Tổng Thu nhập</h3>
            <p><?php echo number_format($total_income, 0); ?> VNĐ</p>
        </div>
        <div class="box expense">
            <h3>Tổng Chi tiêu</h3>
            <p>-<?php echo number_format($total_expense, 0); ?> VNĐ</p>
        </div>
        <div class="box balance">
            <h3>Số dư còn lại</h3>
            <p><?php echo number_format($balance, 0); ?> VNĐ</p>
        </div>
    </div>

    <hr>
    
    <h3>Biểu đồ so sánh Thu nhập và Chi tiêu theo tháng</h3>
    <canvas id="chartMonthlyComparison"></canvas>
    
    <hr>

    <div class="charts-row">
        <div class="chart-box">
            <h3>Phân loại Thu nhập theo Danh mục</h3>
            <canvas id="chartIncomeCategory"></canvas>
        </div>
        <div class="chart-box">
            <h3>Phân loại Chi tiêu theo Danh mục</h3>
            <canvas id="chartExpenseCategory"></canvas>
        </div>
    </div>
    
    <hr>
    
    <h3>Chi tiết Thu nhập (Đã lọc)</h3>
    <table>
        <tr><th>Ngày</th><th>Khoản thu</th><th>Số tiền</th><th>Ghi chú</th></tr>
        <?php if(mysqli_num_rows($list_income) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_income)): ?>
            <tr>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td style="color:#2ecc71; font-weight:bold;"><?php echo number_format($row['amount'], 0); ?> VNĐ</td>
                <td><?php echo htmlspecialchars($row['note']); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">Chưa có dữ liệu thu nhập theo bộ lọc này.</td></tr>
        <?php endif; ?>
    </table>

    <h3>Chi tiết Chi tiêu (Đã lọc)</h3>
    <table>
        <tr><th>Ngày</th><th>Khoản chi</th><th>Số tiền</th><th>Ghi chú</th></tr>
        <?php if(mysqli_num_rows($list_expense) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($list_expense)): ?>
            <tr>
                <td><?php echo $row['date']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td style="color:#e74c3c; font-weight:bold;">-<?php echo number_format($row['amount'], 0); ?> VNĐ</td>
                <td><?php echo htmlspecialchars($row['note']); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center;">Chưa có dữ liệu chi tiêu theo bộ lọc này.</td></tr>
        <?php endif; ?>
    </table>
    
    <div class="back"><a href="index.php">← Quay lại trang chính</a></div>
</div>

<script>
    const months = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
    
    // ========================================================
    // 1. Biểu đồ so sánh Thu/Chi theo tháng
    // ========================================================
    new Chart(document.getElementById('chartMonthlyComparison'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Thu nhập (VNĐ)',
                    data: <?php echo json_encode(array_values($incomeMonthData)); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.7)', // Green for Income
                    borderColor: '#2ecc71',
                    borderWidth: 1
                },
                {
                    label: 'Chi tiêu (VNĐ)',
                    data: <?php echo json_encode(array_values($expenseMonthData)); ?>,
                    backgroundColor: 'rgba(231, 76, 60, 0.7)', // Red for Expense
                    borderColor: '#e74c3c',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // ========================================================
    // 2. Biểu đồ tròn Thu nhập theo Danh mục
    // ========================================================
    new Chart(document.getElementById('chartIncomeCategory'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($catIncomeLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($catIncomeValues); ?>,
                backgroundColor: ['#2ecc71','#3498db','#9b59b6','#f1c40f','#e67e22', '#1abc9c'] // Màu sắc thân thiện
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: false }
            }
        }
    });
    
    // ========================================================
    // 3. Biểu đồ tròn Chi tiêu theo Danh mục
    // ========================================================
    new Chart(document.getElementById('chartExpenseCategory'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($catExpenseLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($catExpenseValues); ?>,
                backgroundColor: ['#e74c3c','#d35400','#f39c12','#c0392b','#7f8c8d', '#2c3e50'] // Màu sắc thân thiện
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: false }
            }
        }
    });
</script>

</body>
</html>