<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập trước!'); window.location='dangnhap.php';</script>";
    exit;
}

// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "Quanlythuchi");
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

$user = $_SESSION['username'];
$message = "";

// Lấy ID người dùng
$user_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username='$user'"))['id'];
// Thêm danh mục chi tiêu mới
if (isset($_POST['them_danhmuc'])) {
    $new_cat = trim($_POST['new_category']);

    if ($new_cat !== "") {
        $check = mysqli_query($conn,
            "SELECT * FROM categories 
            WHERE user_id='$user_id' AND name='$new_cat' AND type='expense'");

        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn,
                "INSERT INTO categories (user_id, name, type) 
                VALUES ('$user_id', '$new_cat', 'expense')");
            $message = "Đã thêm danh mục mới!";
        } else {
            $message = "Danh mục đã tồn tại!";
        }
    }
}


// Thêm chi tiêu
if (isset($_POST['them_chi'])) {
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $note = $_POST['note'];
    $date = $_POST['date'];

    $catName = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM categories WHERE id='$category_id'"))['name'];
    $sql = "INSERT INTO expenses (user_id, category_id, title, amount, note, date)
            VALUES ('$user_id', '$category_id', '$catName', '$amount', '$note', '$date')";
    if (mysqli_query($conn, $sql)) {
        $message = "Đã thêm chi tiêu thành công!";
    } else {
        $message = "Lỗi khi thêm: " . mysqli_error($conn);
    }
}

// Xóa
if (isset($_GET['xoa'])) {
    mysqli_query($conn, "DELETE FROM expenses WHERE id='{$_GET['xoa']}' AND user_id='$user_id'");
    $message = "Đã xóa khoản chi tiêu!";
}

// Cập nhật
if (isset($_POST['capnhat_chi'])) {
    $id = $_POST['id'];
    mysqli_query($conn, "UPDATE expenses SET amount='{$_POST['amount']}', note='{$_POST['note']}', date='{$_POST['date']}' WHERE id='$id'");
    $message = "Đã cập nhật thành công!";
}

$categories = mysqli_query($conn, "SELECT id, name FROM categories WHERE user_id='$user_id' AND type='expense'");
$result = mysqli_query($conn, "SELECT e.id, e.date, c.name AS category, e.amount, e.note 
                               FROM expenses e LEFT JOIN categories c ON e.category_id=c.id 
                               WHERE e.user_id='$user_id' ORDER BY e.date DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý chi tiêu</title>
<style>
    body {
        font-family: "Segoe UI", sans-serif;
        background: linear-gradient(135deg, #fbe3e8, #f5d0d0);
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 850px;
        margin: 40px auto;
        background: #fff;
        padding: 35px;
        border-radius: 16px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #d63031;
        margin-bottom: 20px;
    }
    form {
        background: #fff7f7;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid #f3c1c1;
        margin-bottom: 30px;
    }
    label {
        font-weight: 600;
        color: #333;
        display: block;
        margin-top: 15px;
    }
    input, select, textarea {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 15px;
        transition: all 0.3s;
    }
    input:focus, select:focus, textarea:focus {
        border-color: #d63031;
        box-shadow: 0 0 0 3px rgba(214,48,49,0.1);
        outline: none;
    }
    .row-flex {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 5px;
    }
    .small-btn {
        background: #28a745;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        cursor: pointer;
        font-size: 13px;
    }
    .small-btn:hover { background: #1e7e34; }
    button.submit-btn {
        margin-top: 25px;
        background: #d63031;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px;
        cursor: pointer;
        width: 100%;
        font-weight: bold;
        font-size: 16px;
    }
    button.submit-btn:hover { background: #b71c1c; }
    .message {
        text-align: center;
        font-weight: bold;
        color: #27ae60;
        margin: 15px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 15px;
    }
    th {
        background: #d63031;
        color: white;
        padding: 10px;
    }
    td {
        border-bottom: 1px solid #eee;
        padding: 10px;
    }
    tr:hover { background: #fff0f0; }
    .action-btn {
        border: none;
        padding: 6px 10px;
        border-radius: 5px;
        cursor: pointer;
        color: white;
        font-size: 13px;
    }
    .edit-btn { background: #f1c40f; }
    .delete-btn { background: #e74c3c; }
    .edit-btn:hover { background: #d4ac0d; }
    .delete-btn:hover { background: #c0392b; }
    .hidden-form {
        display: none;
        background: #ffeaea;
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
    }
    .back { text-align: center; margin-top: 30px; }
    .back a { color: #d63031; text-decoration: none; font-weight: 600; }
</style>
<script>
function toggleAddCategory() {
    const form = document.getElementById('addCategoryForm');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}
function toggleEditForm(id) {
    const f = document.getElementById('editForm_' + id);
    f.style.display = (f.style.display === 'none' || f.style.display === '') ? 'table-row' : 'none';
}
</script>
</head>
<body>
<div class="container">
    <h2>Quản lý chi tiêu</h2>
    <p style="text-align:center;">Xin chào, <b><?php echo $_SESSION['username']; ?></b></p>

    <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
    <div id="addCategoryForm" class="hidden-form" style="display:none;">
        <form method="POST">
            <input type="text" name="new_category" placeholder="Nhập tên danh mục mới..." required>
            <button type="submit" name="them_danhmuc" class="small-btn">Lưu</button>
        </form>
    </div>
    <form method="POST">
        <label>Thêm chi tiêu:</label>
        <div class="row-flex">
            <select name="category_id" required>
                <option value="">-- Chọn danh mục --</option>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endwhile; ?>
            </select>
            <button type="button" class="small-btn" onclick="toggleAddCategory()">Thêm</button>
        </div>

        <label>Số tiền (VNĐ):</label>
        <input type="number" name="amount" step="0.01" placeholder="Nhập số tiền..." required>

        <label>Ngày chi:</label>
        <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>">

        <label>Ghi chú:</label>
        <textarea name="note" rows="3" placeholder="Ví dụ: Tiền ăn, hóa đơn, xăng xe..."></textarea>

        <button type="submit" name="them_chi" class="submit-btn">Thêm chi tiêu</button>
    </form>

    <h3 style="margin-bottom:10px;">Danh sách chi tiêu</h3>
    <table>
        <tr>
            <th>Ngày</th>
            <th>Danh mục</th>
            <th>Số tiền (VNĐ)</th>
            <th>Ghi chú</th>
            <th>Hành động</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['date']; ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td style="color:#d63031; font-weight:bold;">-<?php echo number_format($row['amount'], 0); ?></td>
            <td><?php echo htmlspecialchars($row['note']); ?></td>
            <td>
                <button class="action-btn edit-btn" onclick="toggleEditForm(<?php echo $row['id']; ?>)">Sửa</button>
                <a href="?xoa=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa khoản chi này?');">
                    <button type="button" class="action-btn delete-btn">Xóa</button>
                </a>
            </td>
        </tr>
        <tr id="editForm_<?php echo $row['id']; ?>" style="display:none;">
            <td colspan="5">
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <label>Số tiền:</label>
                    <input type="number" name="amount" value="<?php echo $row['amount']; ?>" required>
                    <label>Ghi chú:</label>
                    <input type="text" name="note" value="<?php echo htmlspecialchars($row['note']); ?>">
                    <label>Ngày:</label>
                    <input type="date" name="date" value="<?php echo $row['date']; ?>">
                    <button type="submit" name="capnhat_chi" class="small-btn" style="background:#f1c40f;">Lưu thay đổi</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="thongke_chitieu.php" class="small-btn" >Thống kê chi tiêu</a>

    <div class="back">
        <a href="index.php">← Quay lại trang chính</a>
    </div>
    

</div>
</body>
</html>
