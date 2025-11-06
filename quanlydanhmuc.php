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

// ======================
// 1️⃣ THÊM DANH MỤC MỚI
// ======================
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];

    if ($name == "") {
        $message = "⚠️ Tên danh mục không được bỏ trống!";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM categories WHERE name='$name' AND user_id='$user_id'");
        if (mysqli_num_rows($check) > 0) {
            $message = "❌ Danh mục này đã tồn tại!";
        } else {
            $sql = "INSERT INTO categories (user_id, name, type) VALUES ('$user_id', '$name', '$type')";
            if (mysqli_query($conn, $sql)) {
                $message = "✅ Thêm danh mục thành công!";
            } else {
                $message = "❌ Lỗi khi thêm: " . mysqli_error($conn);
            }
        }
    }
}

// ======================
// 2️⃣ XÓA DANH MỤC
// ======================
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    echo "<script>alert('Đã xóa danh mục thành công!'); window.location='quanlydanhmuc.php';</script>";
    exit;
}

// ======================
// 3️⃣ CẬP NHẬT DANH MỤC
// ======================
if (isset($_POST['update_category'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $type = $_POST['type'];

    if ($name == "") {
        $message = "⚠️ Tên danh mục không được bỏ trống!";
    } else {
        $sql = "UPDATE categories SET name='$name', type='$type' WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            $message = "✅ Cập nhật danh mục thành công!";
        } else {
            $message = "❌ Lỗi khi cập nhật: " . mysqli_error($conn);
        }
    }
}

// ======================
// 4️⃣ HIỂN THỊ DANH MỤC
// ======================
if ($role === 'admin') {
    $sql = "SELECT c.id, c.name, c.type, c.created_at, u.username 
            FROM categories c 
            JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC";
} else {
    $sql = "SELECT * FROM categories WHERE user_id='$user_id' ORDER BY created_at DESC";
}

$categories = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý danh mục</title>
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
    text-align: center;
    color: #d35400;
}
.message {
    text-align: center;
    color: #c0392b;
    font-weight: bold;
}
form.add-form {
    margin-top: 20px;
    text-align: center;
    background: #fff8ec;
    padding: 20px;
    border-radius: 10px;
}
input, select {
    padding: 8px;
    margin: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}
button {
    background: #d35400;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    cursor: pointer;
}
button:hover {
    background: #e67e22;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
}
th, td {
    border-bottom: 1px solid #eee;
    padding: 10px;
    text-align: center;
}
th {
    background: #fff5e1;
}
tr:hover {
    background: #fdf2e9;
}
.action-btn {
    text-decoration: none;
    background: #e74c3c;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
}
.action-btn:hover {
    background: #c0392b;
}
.back {
    text-align: center;
    margin-top: 20px;
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
    <h2>📂 Quản lý danh mục</h2>
    <p class="message"><?php echo $message; ?></p>

    <!-- Form thêm danh mục -->
    <form method="POST" class="add-form">
        <h3>➕ Thêm danh mục mới</h3>
        <input type="text" name="name" placeholder="Tên danh mục" required>
        <select name="type">
            <option value="income">Thu nhập</option>
            <option value="expense">Chi tiêu</option>
        </select>
        <button type="submit" name="add_category">Thêm</button>
    </form>

    <!-- Bảng danh sách -->
    <table>
        <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Loại</th>
            <?php if ($role === 'admin'): ?><th>Người tạo</th><?php endif; ?>
            <th>Ngày tạo</th>
            <th>Thao tác</th>
        </tr>

        <?php if (mysqli_num_rows($categories) > 0): ?>
            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                <tr>
                    <form method="POST">
                        <td><?php echo $cat['id']; ?><input type="hidden" name="id" value="<?php echo $cat['id']; ?>"></td>
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
                            <button type="submit" name="update_category">💾 Lưu</button>
                            <a class="action-btn" href="quanlydanhmuc.php?delete_id=<?php echo $cat['id']; ?>" onclick="return confirm('Xóa danh mục này?');">Xóa</a>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Chưa có danh mục nào</td></tr>
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
