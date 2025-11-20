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


// 3. Thống kê thu chi theo tháng (cho biểu đồ cột)
$current_year = date("Y");
$months = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
$income_values = array_fill(0, 12, 0);
$expense_values = array_fill(0, 12, 0);

// Lấy dữ liệu thu nhập
$sql_income_monthly = "SELECT MONTH(date) as month, SUM(amount) as total FROM incomes WHERE YEAR(date) = '$current_year' GROUP BY MONTH(date)";
$result_income = mysqli_query($conn, $sql_income_monthly);
while ($row = mysqli_fetch_assoc($result_income)) {
    $income_values[$row['month'] - 1] = (int)$row['total'];
}

// Lấy dữ liệu chi tiêu
$sql_expense_monthly = "SELECT MONTH(date) as month, SUM(amount) as total FROM expenses WHERE YEAR(date) = '$current_year' GROUP BY MONTH(date)";
$result_expense = mysqli_query($conn, $sql_expense_monthly);
while ($row = mysqli_fetch_assoc($result_expense)) {
    $expense_values[$row['month'] - 1] = (int)$row['total'];
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê Hệ thống</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            background-color: #f0f2f5; 
            color: #333; 
            margin: 0; 
            padding: 0; 
        }

        /* 💥 NEW NAVBAR CSS (Consistent with admin.php) */
        .navbar {
            background-color: #2a5dca; /* Main Blue */
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky; 
            top: 0;
            z-index: 1000;
        }
        .navbar-left {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .navbar-left a, .navbar-right a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
            white-space: nowrap; 
        }
        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
            color: white !important;
            padding: 0 15px 0 0;
            background: none !important;
            border-right: 1px solid rgba(255,255,255,0.3);
        }
        .navbar-left a:not(.navbar-brand):hover {
            background-color: #3e73d4;
        }
        /* 💥 Active Link */
        .navbar-left a[href*="thongke_hethong"] {
            background-color: #1a47a1; /* Darker blue to indicate active */
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .navbar-right a {
            background-color: #e74c3c; 
        }
        .navbar-right a:hover {
            background-color: #c0392b;
        }
        /* END NAVBAR CSS */

        .container { 
            max-width: 1200px; /* Tăng chiều rộng để chứa 2 biểu đồ */
            margin: 40px auto; 
            background: #ffffff; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); 
        }
        h2 { 
            text-align: center; 
            color: #2a5dca; 
            margin-bottom: 30px; 
            font-size: 28px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }
        h3 {
            text-align: center;
            color: #444;
            margin-top: 20px;
            margin-bottom: 15px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border-left: 5px solid #2a5dca;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card span {
            display: block;
            font-size: 24px;
            color: #2a5dca;
            margin-top: 5px;
            font-weight: 900;
        }
        .card:nth-child(1) { border-left-color: #3498db; } /* Users */
        .card:nth-child(2) { border-left-color: #9b59b6; } /* Categories */
        .card:nth-child(3) { border-left-color: #2ecc71; } /* Incomes */
        .card:nth-child(4) { border-left-color: #e74c3c; } /* Expenses */
        .card:nth-child(5) { border-left-color: #f1c40f; } /* Balance */
        .card:nth-child(3) span { color: #2ecc71; }
        .card:nth-child(4) span { color: #e74c3c; }

        /* --- NEW CHART LAYOUT CSS --- */
        .chart-row {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
        }
        .chart-container-half {
            flex: 1;
            min-width: 0; 
            height: 450px; /* Chiều cao cố định cho 2 biểu đồ */
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        /* Tùy chỉnh cho biểu đồ tròn để nó không bị kéo dãn */
        #chartRatio {
            max-width: 90%;
            max-height: 90%;
            margin: 0 auto;
        }

        .back {
            text-align: center;
            margin-top: 30px;
        }
        .back a {
            color: #2a5dca;
            text-decoration: none;
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .card:nth-child(5) { grid-column: span 2; } /* Balance card span 2 on smaller screen */

            .chart-row {
                flex-direction: column; /* Xếp chồng biểu đồ trên màn hình nhỏ */
            }
            .chart-container-half {
                height: 400px; /* Điều chỉnh chiều cao cho màn hình nhỏ */
            }
        }
        @media (max-width: 576px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
            .card:nth-child(5) { grid-column: span 1; }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="navbar-left">
        <a href="admin.php" class="navbar-brand"><i class="fas fa-shield-alt"></i> ADMIN PANEL</a>
        <a href="quanlynguoidung.php"><i class="fas fa-users-cog"></i> Quản lý Người dùng</a>
        <a href="quanlydanhmuc.php"><i class="fas fa-tags"></i> Quản lý Danh mục</a>
        <a href="thongke_hethong.php" class="active"><i class="fas fa-chart-line"></i> Thống kê Hệ thống</a>
    </div>
    <div class="navbar-right">
        <a href="dangxuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </div>
</nav>
<div class="container">
    <h2><i class="fas fa-chart-line"></i> Thống kê Tổng quan Hệ thống</h2>

    <div class="summary-grid">
        <div class="card"> Tổng Người dùng<span><?php echo number_format($total_users); ?></span></div>
        <div class="card"> Tổng Danh mục<span><?php echo number_format($total_categories); ?></span></div>
        <div class="card"> Tổng Khoản thu<span><?php echo number_format($total_incomes) . " (" . number_format($total_income_money) . " VNĐ)"; ?></span></div>
        <div class="card"> Tổng Khoản chi<span><?php echo number_format($total_expenses) . " (" . number_format($total_expense_money) . " VNĐ)"; ?></span></div>
        <div class="card" style="grid-column: span 4;"> Số dư Hệ thống<span style="color:#27ae60;"><?php echo number_format($total_balance); ?> VNĐ</span></div>
    </div>
    
    <div class="chart-row">
        <div class="chart-container-half">
            <h3><i class="fas fa-chart-pie"></i> Phân bổ Thu/Chi (Toàn Hệ thống)</h3>
            <canvas id="chartRatio"></canvas>
        </div>

        <div class="chart-container-half">
            <h3><i class="fas fa-chart-bar"></i> Thu chi theo tháng (Năm <?php echo $current_year; ?>)</h3>
            <canvas id="chartThuChi"></canvas>
        </div>
    </div>
</div>

<script>
// Cấu hình ngôn ngữ cho định dạng tiền tệ
const currencyFormatter = new Intl.NumberFormat('vi-VN', { 
    style: 'currency', 
    currency: 'VND',
    minimumFractionDigits: 0 // Bỏ số thập phân
});
const numberFormatter = new Intl.NumberFormat('vi-VN');


// ===================================
// 1. Biểu đồ Tỷ lệ Thu/Chi (Doughnut Chart)
// ===================================
const ctxRatio = document.getElementById('chartRatio');
new Chart(ctxRatio, {
    type: 'doughnut',
    data: {
        labels: ['Tổng Thu nhập', 'Tổng Chi tiêu'],
        datasets: [{
            data: [<?php echo $total_income_money; ?>, <?php echo $total_expense_money; ?>],
            backgroundColor: [
                '#2ecc71', // Green for Income (Thu nhập)
                '#e74c3c'  // Red for Expense (Chi tiêu)
            ],
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            title: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed !== null) {
                            // Định dạng giá trị thành tiền tệ
                            label += currencyFormatter.format(context.parsed);
                        }
                        return label;
                    }
                }
            }
        }
    }
});


// ===================================
// 2. Biểu đồ Thu chi theo tháng (Bar Chart)
// ===================================
const ctxBar = document.getElementById('chartThuChi');
new Chart(ctxBar, {
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
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Số tiền (VNĐ)'
                },
                ticks: {
                    // Định dạng y-axis labels
                    callback: function(value, index, ticks) {
                        return numberFormatter.format(value);
                    }
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Tháng'
                }
            }
        },
        plugins: {
            title: {
                display: false 
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            // Định dạng tooltip thành tiền tệ
                            label += currencyFormatter.format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>