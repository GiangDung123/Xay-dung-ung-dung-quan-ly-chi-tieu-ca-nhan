<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: dangnhap.php");
    exit;
}

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$database = "Quanlythuchi";
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$message = "";
$user = $_SESSION['username'];

// Lấy ID người dùng hiện tại
$result = mysqli_query($conn, "SELECT id, role FROM users WHERE username='$user'");
$row = mysqli_fetch_assoc($result);
$user_id = $row['id'];
$role = $row['role'];

// =======================
// 1️⃣ THÊM DANH MỤC MỚI
// =======================
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];

    if ($name == "") {
        $message = "⚠️ Tên danh mục không được bỏ trống!";
    } else {
        // Lọc dữ liệu đầu vào
        $name = mysqli_real_escape_string($conn, $name);
        $type = mysqli_real_escape_string($conn, $type);

        // Đối với Admin, danh mục tạo ra là của Admin (user_id=1 nếu Admin là id=1, hoặc dùng id của người đang login)
        // Hiện tại, ta dùng ID của người đang login. Nếu Admin tạo danh mục dùng chung, cần cấu trúc DB khác.
        // Giả định: Admin quản lý danh mục của mình hoặc danh mục hệ thống (nếu có user_id=0). 
        // Trong trường hợp này, Admin đang quản lý danh mục của chính tài khoản Admin.
        
        $check = mysqli_query($conn, "SELECT * FROM categories WHERE name='$name' AND user_id='$user_id'");
        if (mysqli_num_rows($check) > 0) {
            $message = "❌ Danh mục đã tồn tại trong danh sách của bạn!";
        } else {
            $sql = "INSERT INTO categories (user_id, name, type) VALUES ('$user_id', '$name', '$type')";
            if (mysqli_query($conn, $sql)) {
                $message = "✅ Thêm danh mục **$name** thành công!";
            } else {
                $message = "❌ Lỗi: " . mysqli_error($conn);
            }
        }
    }
}

// =======================
// 2️⃣ CẬP NHẬT DANH MỤC
// =======================
if (isset($_POST['update_category'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    
    // Lọc dữ liệu đầu vào
    $id = mysqli_real_escape_string($conn, $id);
    $name = mysqli_real_escape_string($conn, $name);
    $type = mysqli_real_escape_string($conn, $type);

    $sql = "UPDATE categories SET name='$name', type='$type' WHERE id='$id'";
    // Nếu là user thường, cần thêm điều kiện WHERE user_id='$user_id' để ngăn họ chỉnh sửa danh mục của người khác.
    if ($role === 'user') {
        $sql .= " AND user_id='$user_id'";
    }

    if (mysqli_query($conn, $sql)) {
        $message = "✅ Cập nhật danh mục thành công!";
    } else {
        $message = "❌ Lỗi: " . mysqli_error($conn);
    }
}

// =======================
// 3️⃣ XÓA DANH MỤC
// =======================
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $id = mysqli_real_escape_string($conn, $id);
    
    $sql = "DELETE FROM categories WHERE id='$id'";
    // Nếu là user thường, cần thêm điều kiện WHERE user_id='$user_id' để ngăn họ xóa danh mục của người khác.
    if ($role === 'user') {
        $sql .= " AND user_id='$user_id'";
    }

    if (mysqli_query($conn, $sql)) {
        // Cần thêm bước xóa các giao dịch (income/expense) sử dụng danh mục này trước, 
        // hoặc đặt NULL cho trường category_id. (Tùy thuộc vào foreign key settings). 
        // Hiện tại, ta chỉ xóa danh mục.
        $message = "✅ Xóa danh mục (ID: $id) thành công!";
    } else {
        $message = "❌ Lỗi xóa danh mục: " . mysqli_error($conn);
    }
    header("Location: quanlydanhmuc.php");
    exit;
}

// =======================
// 4️⃣ LẤY DANH SÁCH DANH MỤC
// =======================
$sql_categories = "SELECT c.*, u.username FROM categories c JOIN users u ON c.user_id = u.id";

if ($role === 'user') {
    // User chỉ thấy danh mục của mình
    $sql_categories .= " WHERE c.user_id='$user_id'";
} 
// Admin thấy tất cả, sắp xếp theo ngày tạo
$sql_categories .= " ORDER BY c.created_at DESC";

$categories_query = mysqli_query($conn, $sql_categories);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($role === 'admin' ? 'Quản trị' : 'Cá nhân'); ?> - Quản lý Danh mục</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS cho quanlydanhmuc.php */
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
        .navbar-left a.active {
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
            max-width: 900px; 
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

        /* Form Thêm danh mục */
        .add-category-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #eee;
        }
        .add-category-form h3 {
            margin-top: 0;
            color: #34495e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 100px; 
            gap: 15px;
            align-items: end;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 14px;
        }
        .add-category-form input[type="text"],
        .add-category-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .add-category-form button {
            padding: 10px 15px;
            background-color: #2ecc71; 
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        .add-category-form button:hover {
            background-color: #27ae60;
        }

        /* Bảng Quản lý Danh mục */
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
        td input[type="text"] { width: 150px; }
        td select { width: 100px; }
        td input[type="text"], 
        td select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Nút hành động */
        button[name="update_category"] {
            background-color: #3498db; 
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }
        button[name="update_category"]:hover {
            background-color: #2980b9;
        }
        a.action-btn {
            background-color: #e74c3c; 
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
        }
        a.action-btn:hover {
            background-color: #c0392b;
        }

        .back {
            margin-top: 20px;
            text-align: center;
        }
        .back a {
            color: #2a5dca;
            text-decoration: none;
            font-weight: bold;
        }
        
    </style>
</head>
<body>
    <?php if ($role === 'admin'): ?>
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
    <?php else: ?>
        <nav class="navbar">
            <div class="navbar-left">
                <a href="index.php" class="navbar-brand"><i class="fas fa-wallet"></i> Quản Lý Chi Tiêu</a>
                <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <a href="quanlydanhmuc.php" class="active"><i class="fas fa-tags"></i> Quản lý Danh mục</a>
            </div>
            <div class="navbar-right">
                <a href="dangxuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        </nav>
    <?php endif; ?>

<div class="container">
    <h2><i class="fas fa-tags"></i> Quản lý Danh mục <?php echo ($role === 'admin' ? '(Hệ thống)' : '(Cá nhân)'); ?></h2>

    <?php 
    if ($message) {
        $class = strpos($message, 'thành công') !== false ? 'success' : (strpos($message, 'Lỗi') !== false || strpos($message, '❌') !== false ? 'error' : 'warning');
        echo "<div class='message $class'>" . nl2br($message) . "</div>";
    }
    ?>

    <div class="add-category-form">
        <h3><i class="fas fa-plus-circle"></i> Thêm Danh mục Mới</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Tên danh mục:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Loại:</label>
                    <select name="type">
                        <option value="income">Thu nhập</option>
                        <option value="expense">Chi tiêu</option>
                    </select>
                </div>
                <button type="submit" name="add_category"><i class="fas fa-save"></i> Thêm</button>
            </div>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Tên Danh mục</th>
            <th>Loại</th>
            <?php if ($role === 'admin'): ?>
                <th>Người tạo</th>
            <?php endif; ?>
            <th>Ngày tạo</th>
            <th>Hành động</th>
        </tr>
        <?php if (mysqli_num_rows($categories_query) > 0): ?>
            <?php while ($cat = mysqli_fetch_assoc($categories_query)): ?>
                <tr>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                        <td><?php echo $cat['id']; ?></td>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>"></td>
                        <td>
                            <select name="type">
                                <option value="income" <?php if ($cat['type']=='income') echo 'selected'; ?>>Thu nhập</option>
                                <option value="expense" <?php if ($cat['type']=='expense') echo 'selected'; ?>>Chi tiêu</option>
                            </select>
                        </td>
                        <?php if ($role === 'admin'): ?>
                            <td><?php echo $cat['username']; ?></td>
                        <?php endif; ?>
                        <td><?php echo $cat['created_at']; ?></td>
                        <td>
                            <button type="submit" name="update_category"><i class="fas fa-save"></i> Lưu</button>
                            <a class="action-btn" href="quanlydanhmuc.php?delete_id=<?php echo $cat['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');"><i class="fas fa-trash"></i> Xóa</a>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="<?php echo $role === 'admin' ? '6' : '5'; ?>">Chưa có danh mục nào</td></tr>
        <?php endif; ?>
    </table>

    <div class="back">
        <?php if ($role === 'admin'): ?>
            <a href="admin.php">← Quay lại trang quản trị</a>
        <?php else: ?>
            <a href="index.php">← Quay lại trang chính</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>