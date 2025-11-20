<?php
session_start();

// Kiểm tra quyền truy cập (chỉ admin)
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: dangnhap.php");
    exit;
}

// 1️⃣ Kết nối CSDL (Giữ lại để đảm bảo tính nhất quán)
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Đóng kết nối DB
mysqli_close($conn); 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển Admin</title>
    <style>
        /* Modern Reset/Base Styles */
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            background-color: #f0f2f5; 
            color: #1c1e21; 
            margin: 0; 
            padding: 20px; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container { 
            width: 100%;
            max-width: 900px; /* Tăng kích thước tối đa */
            background: #ffffff; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); 
            text-align: center; 
        }
        
        /* Header */
        h2 { 
            color: #007bff; /* Màu xanh dương hiện đại */
            margin-bottom: 5px; 
            font-size: 2.2rem;
            font-weight: 700;
        }
        p { 
            margin-bottom: 40px; 
            color: #606770; 
            font-size: 1.1rem;
        }

        /* Grid Layout */
        .dashboard-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Cột linh hoạt */
            gap: 25px; 
            margin-top: 30px;
        }

        /* Dashboard Item - Sử dụng thẻ <a> làm khối chính để cả khối là link */
        .dashboard-item {
            text-decoration: none; /* Bỏ gạch chân link */
            background-color: #f7f9fa; /* Nền nhẹ */
            padding: 30px; 
            border-radius: 12px; 
            text-align: center; 
            transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s;
            border: 1px solid #e1e4e8;
            display: flex; /* Dùng flexbox để căn giữa nội dung */
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #1c1e21; /* Màu chữ mặc định */
        }
        .dashboard-item:hover {
            transform: translateY(-8px); /* Nhấc lên khi hover */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15); /* Shadow sâu hơn */
            background-color: #eaf3ff; /* Nền xanh nhạt khi hover */
        }
        
        .dashboard-item i {
            font-size: 48px; /* Icon to hơn */
            color: #007bff;
            margin-bottom: 15px;
            display: block;
            line-height: 1; /* Cân bằng icon */
        }

        .dashboard-item span {
            color: #007bff;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        /* Logout Button */
        .logout { margin-top: 40px; }
        .logout a { 
            color: #dc3545; /* Màu đỏ cho Đăng xuất */
            text-decoration: none; 
            font-weight: 600; 
            padding: 12px 30px; 
            border: 2px solid #dc3545; 
            border-radius: 8px; 
            transition: all 0.3s; 
            display: inline-block;
            font-size: 1rem;
        }
        .logout a:hover { 
            background-color: #dc3545; 
            color: white; 
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>👋 Chào mừng Admin, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Bảng điều khiển quản trị hệ thống.</p>
    
    <div class="dashboard-grid">
        
        <a href="quanlynguoidung.php" class="dashboard-item">
            <i>👥</i>
            <span>Quản lý Người dùng</span>
        </a>

        <a href="quanlydanhmuc.php" class="dashboard-item">
            <i>🏷️</i>
            <span>Quản lý Danh mục</span>
        </a>

        <a href="thongke_hethong.php" class="dashboard-item">
            <i>📈</i>
            <span>Thống kê Hệ thống</span>
        </a>
    </div>

    <div class="logout">
        <a href="dangxuat.php">Đăng xuất</a>
    </div>
</div>
</body>
</html>