<?php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: dangnhap.php");
    exit;
}

// 1️⃣ Kết nối CSDL
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$message = "";

// 2️⃣ Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    $uname = trim($_POST['username']);
    $pass = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $role = $_POST['role'];

    if ($uname == "" || $pass == "") {
        $message = "⚠️ Tên đăng nhập và mật khẩu không được bỏ trống!";
    } else {
        // Lọc dữ liệu đầu vào
        $uname = mysqli_real_escape_string($conn, $uname);
        $fullname = mysqli_real_escape_string($conn, $fullname);
        $role = mysqli_real_escape_string($conn, $role);

        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$uname'");
        if (mysqli_num_rows($check) > 0) {
            $message = "❌ Tên đăng nhập đã tồn tại!";
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, fullname, role) VALUES ('$uname', '$hashed', '$fullname', '$role')";
            
            if (mysqli_query($conn, $sql)) {
                $message = "✅ Thêm người dùng **$uname** thành công!";
            } else {
                $message = "❌ Lỗi: " . mysqli_error($conn);
            }
        }
    }
}

// 3️⃣ Xử lý cập nhật người dùng
if (isset($_POST['update_user'])) {
    $id = $_POST['id'];
    $fullname = trim($_POST['fullname']);
    $role = $_POST['role'];
    
    // Lọc dữ liệu đầu vào
    $id = mysqli_real_escape_string($conn, $id);
    $fullname = mysqli_real_escape_string($conn, $fullname);
    $role = mysqli_real_escape_string($conn, $role);

    $sql = "UPDATE users SET fullname='$fullname', role='$role' WHERE id='$id'";
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Cập nhật người dùng thành công!";
    } else {
        $message = "❌ Lỗi: " . mysqli_error($conn);
    }
}

// 4️⃣ Xử lý xóa người dùng
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // Ngăn xóa tài khoản admin chính
    $check_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE id='$id'"));
    if ($check_admin['username'] !== 'admin') {
        // Xóa tất cả giao dịch liên quan
        mysqli_query($conn, "DELETE FROM incomes WHERE user_id='$id'");
        mysqli_query($conn, "DELETE FROM expenses WHERE user_id='$id'");
        mysqli_query($conn, "DELETE FROM categories WHERE user_id='$id'");
        
        // Xóa người dùng
        $sql = "DELETE FROM users WHERE id='$id'";
        if (mysqli_query($conn, $sql)) {
            $message = "✅ Xóa người dùng (ID: $id) và toàn bộ dữ liệu liên quan thành công!";
        } else {
            $message = "❌ Lỗi xóa người dùng: " . mysqli_error($conn);
        }
    } else {
        $message = "❌ Không thể xóa tài khoản Admin chính!";
    }
    // Chuyển hướng để xóa tham số trên URL
    header("Location: admin.php");
    exit;
}


// 5️⃣ Lấy danh sách người dùng
$users_query = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS cho admin.php */
        body { 
            font-family: 'Arial', sans-serif; 
            background-color: #f0f2f5; 
            color: #333; 
            margin: 0; 
            padding: 0; 
        }

        /* 💥 NEW NAVBAR CSS */
        .navbar {
            background-color: #2a5dca; /* Main Blue */
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky; /* Make it stick to the top */
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
            white-space: nowrap; /* Prevent wrapping */
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
        .navbar-right a {
            background-color: #e74c3c; /* Red for logout */
        }
        .navbar-right a:hover {
            background-color: #c0392b;
        }

        /* Container adjustment for sticky navbar */
        .container { 
            max-width: 1200px; 
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

        /* Message Box */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        /* Form Thêm người dùng */
        .add-user-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #eee;
        }
        .add-user-form h3 {
            margin-top: 0;
            color: #34495e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr) 100px; /* 4 input + 1 button */
            gap: 15px;
            align-items: end;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
        }
        .add-user-form input[type="text"],
        .add-user-form input[type="password"],
        .add-user-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .add-user-form button {
            padding: 10px 15px;
            background-color: #2ecc71; /* Green for Add */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .add-user-form button:hover {
            background-color: #27ae60;
        }

        /* Bảng Quản lý Người dùng */
        .user-table-container {
            margin-top: 30px;
        }
        .user-table-container h3 {
            color: #34495e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        th {
            background-color: #2a5dca;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        tr:hover {
            background-color: #eef;
        }

        /* Form chỉnh sửa trong bảng */
        td form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        td input[type="text"], 
        td select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }
        td input[type="text"] { width: 150px; }
        td select { width: 100px; }

        /* Nút hành động trong bảng */
        .action-btn {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-size: 14px;
            transition: background 0.3s, color 0.3s;
        }
        button[name="update_user"] {
            background-color: #3498db; /* Blue for Save */
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }
        button[name="update_user"]:hover {
            background-color: #2980b9;
        }
        a.action-btn[href*="delete_id"] {
            background-color: #e74c3c; /* Red for Delete */
            color: white;
        }
        a.action-btn[href*="delete_id"]:hover {
            background-color: #c0392b;
        }
        
        /* Highlight Admin Role */
        .admin-role {
            font-weight: bold;
            color: #2a5dca; /* Blue color */
        }
        .user-role {
            color: #7f8c8d; /* Gray color */
        }

        /* Responsive */
        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .navbar-left {
                flex-direction: column;
                gap: 5px;
                width: 100%;
            }
            .navbar-left a, .navbar-right a {
                padding: 5px 10px;
            }
            .navbar-brand {
                margin-bottom: 10px;
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,0.3);
                padding-bottom: 5px;
            }
            .navbar-right {
                margin-top: 10px;
            }
        }
        /* Remove old menu/logout styles as they are now in navbar */
        .admin-menu, .logout { display: none; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <a href="admin.php" class="navbar-brand"><i class="fas fa-shield-alt"></i> ADMIN PANEL</a>
            <a href="quanlynguoidung.php"><i class="fas fa-users-cog"></i> Quản lý Người dùng</a>
            <a href="quanlydanhmuc.php"><i class="fas fa-tags"></i> Quản lý Danh mục</a>
            <a href="thongke_hethong.php"><i class="fas fa-chart-line"></i> Thống kê Hệ thống</a>
        </div>
        <div class="navbar-right">
            <a href="dangxuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </nav>

<div class="container">
    <h2><i class="fas fa-users-cog"></i> Trang Quản lý Người dùng (Admin)</h2>

    <?php 
    if ($message) {
        $class = strpos($message, 'thành công') !== false ? 'success' : (strpos($message, 'Lỗi') !== false || strpos($message, '❌') !== false ? 'error' : 'warning');
        echo "<div class='message $class'>" . nl2br($message) . "</div>";
    }
    ?>

    <div class="add-user-form">
        <h3><i class="fas fa-user-plus"></i> Thêm Người dùng Mới</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Họ tên (Không bắt buộc):</label>
                    <input type="text" name="fullname" value="">
                </div>
                <div class="form-group">
                    <label>Vai trò:</label>
                    <select name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user"><i class="fas fa-save"></i> Thêm</button>
            </div>
        </form>
    </div>

    <div class="user-table-container">
        <h3><i class="fas fa-list"></i> Danh sách Người dùng</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên Đăng nhập</th>
                <th>Họ & Tên</th>
                <th>Vai trò</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
            <?php if (mysqli_num_rows($users_query) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($users_query)): ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><input type="text" name="fullname" value="<?php echo htmlspecialchars($row['fullname']); ?>"></td>
                            <td class="<?php echo $row['role']=='admin' ? 'admin-role' : 'user-role'; ?>">
                                <select name="role">
                                    <option value="user" <?php if ($row['role']=='user') echo 'selected'; ?>>User</option>
                                    <option value="admin" <?php if ($row['role']=='admin') echo 'selected'; ?>>Admin</option>
                                </select>
                            </td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <?php if ($row['username'] !== 'admin'): ?>
                                    <button type="submit" name="update_user"><i class="fas fa-save"></i> Lưu</button>
                                    <a class="action-btn" href="admin.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này? Thao tác này sẽ xóa TẤT CẢ giao dịch của họ.');"><i class="fas fa-trash"></i> Xóa</a>
                                <?php else: ?>
                                    <span style="color:gray;"><i class="fas fa-lock"></i> (Admin Chính)</span>
                                <?php endif; ?>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Chưa có người dùng nào</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>
</body>
</html>