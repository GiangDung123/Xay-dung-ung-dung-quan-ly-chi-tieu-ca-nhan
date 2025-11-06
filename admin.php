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
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$uname'");
        if (mysqli_num_rows($check) > 0) {
            $message = "❌ Tên đăng nhập đã tồn tại!";
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, fullname, role) VALUES ('$uname', '$hashed', '$fullname', '$role')";
            if (mysqli_query($conn, $sql)) {
                $message = "✅ Thêm người dùng thành công!";
            } else {
                $message = "❌ Lỗi khi thêm: " . mysqli_error($conn);
            }
        }
    }
}

// 3️⃣ Xử lý xóa người dùng
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $query = mysqli_query($conn, "SELECT username FROM users WHERE id=$id");
    $r = mysqli_fetch_assoc($query);
    if ($r && $r['username'] !== 'admin') {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        echo "<script>alert('Đã xóa người dùng thành công!'); window.location='admin.php';</script>";
        exit;
    } else {
        $message = "⚠️ Không thể xóa tài khoản admin!";
    }
}

// 4️⃣ Xử lý cập nhật người dùng
if (isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $fullname = trim($_POST['fullname']);
    $role = $_POST['role'];

    $sql = "UPDATE users SET fullname='$fullname', role='$role' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        $message = "✅ Cập nhật thông tin người dùng thành công!";
    } else {
        $message = "❌ Lỗi cập nhật: " . mysqli_error($conn);
    }
}

// 5️⃣ Lấy danh sách người dùng
$result = mysqli_query($conn, "SELECT id, username, fullname, role, created_at FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản trị hệ thống - Quản lý thu chi</title>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #fef9e7, #fdebd0);
    margin: 0;
    padding: 0;
}
.container {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #d35400;
    margin-bottom: 25px;
}
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.top-bar a {
    text-decoration: none;
    padding: 10px 20px;
    background: #d35400;
    color: white;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.2s;
}
.top-bar a:hover {
    background: #e67e22;
}
.message {
    text-align: center;
    color: #c0392b;
    font-weight: 600;
    margin-bottom: 10px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    border-bottom: 1px solid #eee;
    text-align: center;
    padding: 10px;
    font-size: 15px;
}
th {
    background: #fff5e1;
    color: #2c3e50;
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
form.add-user, form.update-user {
    margin: 25px 0;
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
.logout {
    text-align: right;
    margin-top: 15px;
}
.logout a {
    color: #d63031;
    text-decoration: none;
    font-weight: bold;
}
.logout a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="container">
    <h2>👑 Quản trị người dùng hệ thống</h2>

    <div class="top-bar">
        <a href="thongke_hethong.php">📊 Thống kê hệ thống</a>
        <a href="quanlydanhmuc.php">📂 Quản lý danh mục</a>
    </div>

    <p class="message"><?php echo $message; ?></p>

    <!-- Form thêm người dùng -->
    <form method="POST" class="add-user">
        <h3>➕ Thêm người dùng mới</h3>
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <input type="text" name="fullname" placeholder="Họ và tên">
        <select name="role">
            <option value="user">Người dùng</option>
            <option value="admin">Quản trị viên</option>
        </select>
        <button type="submit" name="add_user">Thêm</button>
    </form>

    <!-- Danh sách người dùng -->
    <table>
        <tr>
            <th>ID</th>
            <th>Tên đăng nhập</th>
            <th>Họ tên</th>
            <th>Quyền</th>
            <th>Ngày tạo</th>
            <th>Thao tác</th>
        </tr>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <form method="POST" class="update-user">
                        <td><?php echo $row['id']; ?><input type="hidden" name="user_id" value="<?php echo $row['id']; ?>"></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><input type="text" name="fullname" value="<?php echo htmlspecialchars($row['fullname']); ?>"></td>
                        <td>
                            <select name="role">
                                <option value="user" <?php if ($row['role']=='user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if ($row['role']=='admin') echo 'selected'; ?>>Admin</option>
                            </select>
                        </td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <?php if ($row['username'] !== 'admin'): ?>
                                <button type="submit" name="update_user">💾 Lưu</button>
                                <a class="action-btn" href="admin.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Xóa người dùng này?');">Xóa</a>
                            <?php else: ?>
                                <span style="color:gray;">(Admin chính)</span>
                            <?php endif; ?>
                        </td>
                    </form>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Chưa có người dùng nào</td></tr>
        <?php endif; ?>
    </table>

    <div class="logout">
        <a href="dangxuat.php">Đăng xuất</a>
    </div>
</div>
</body>
</html>
