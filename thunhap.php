<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo "<script>alert('Vui lòng đăng nhập trước!'); window.location='dangnhap.php';</script>";
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "Quanlythuchi");
if (!$conn) die("Kết nối thất bại: " . mysqli_connect_error());

$user = $_SESSION['username'];
$message = "";

// Lấy ID người dùng
$user_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username='$user'"))['id'];

// Thêm danh mục
if (isset($_POST['them_danhmuc'])) {
    $new_cat = trim($_POST['new_category']);
    if ($new_cat != "") {
        $check = mysqli_query($conn, "SELECT * FROM categories WHERE user_id='$user_id' AND name='$new_cat' AND type='income'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO categories (user_id, name, type) VALUES ('$user_id', '$new_cat', 'income')");
            $message = "Đã thêm danh mục mới!";
        } else $message = "Danh mục đã tồn tại!";
    }
}

// Thêm thu nhập
if (isset($_POST['them_thunhap'])) {
    $cat = $_POST['category_id'];
    $amount = $_POST['amount'];
    $note = $_POST['note'];
    $date = $_POST['date'];
    $catName = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM categories WHERE id='$cat'"))['name'];
    mysqli_query($conn, "INSERT INTO incomes (user_id, category_id, title, amount, note, date)
                         VALUES ('$user_id','$cat','$catName','$amount','$note','$date')");
    $message = "Đã thêm thu nhập!";
}

// Xóa
if (isset($_GET['xoa'])) {
    mysqli_query($conn, "DELETE FROM incomes WHERE id='{$_GET['xoa']}' AND user_id='$user_id'");
    $message = "Đã xóa khoản thu nhập!";
}

// Cập nhật
if (isset($_POST['capnhat_thu'])) {
    $id = $_POST['id'];
    mysqli_query($conn, "UPDATE incomes SET amount='{$_POST['amount']}', note='{$_POST['note']}', date='{$_POST['date']}' WHERE id='$id'");
    $message = "Đã cập nhật thành công!";
}

$categories = mysqli_query($conn, "SELECT id, name FROM categories WHERE user_id='$user_id' AND type='income'");
$result = mysqli_query($conn, "SELECT i.id, i.date, c.name AS category, i.amount, i.note 
                               FROM incomes i LEFT JOIN categories c ON i.category_id=c.id 
                               WHERE i.user_id='$user_id' ORDER BY i.date DESC");
// =============================
// XỬ LÝ THÊM DANH MỤC
// =============================
if (isset($_POST['them_danhmuc'])) {
    $new_cat = trim($_POST['new_category']);

    if ($new_cat !== "") {
        $check = mysqli_query($conn,
            "SELECT * FROM categories 
             WHERE user_id='$user_id' AND name='$new_cat' AND type='income'");

        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn,
                "INSERT INTO categories (user_id, name, type) 
                 VALUES ('$user_id', '$new_cat', 'income')");
            $message = "Đã thêm danh mục mới!";
        } else {
            $message = "Danh mục đã tồn tại!";
        }
    }
}


?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý thu nhập</title>
<style>
    body {
        font-family: "Segoe UI", sans-serif;
        background: linear-gradient(135deg, #eef2f3, #dfe9f3);
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
        color: #0078ff;
        margin-bottom: 20px;
    }
    p {
        text-align: center;
        color: #444;
    }
    form {
        background: #f9fbff;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid #e0e7ff;
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
        border-color: #0078ff;
        box-shadow: 0 0 0 3px rgba(0,120,255,0.1);
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
        background: #0078ff;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px;
        cursor: pointer;
        width: 100%;
        font-weight: bold;
        font-size: 16px;
    }
    button.submit-btn:hover { background: #005fcc; }
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
        background: #0078ff;
        color: white;
        padding: 10px;
    }
    td {
        border-bottom: 1px solid #eee;
        padding: 10px;
    }
    tr:hover { background: #f9faff; }
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
        background: #eef6ff;
        padding: 10px;
        border-radius: 8px;
        margin-top: 10px;
    }
    .back { text-align: center; margin-top: 30px; }
    .back a { color: #0078ff; text-decoration: none; font-weight: 600; }
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
    <h2>Thêm thu nhập</h2>
    <p>Xin chào, <b><?php echo $_SESSION['username']; ?></b></p>

    <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
        
    <div id="addCategoryForm" class="hidden-form" style="display:none;">
        <form method="POST">
            <input type="text" name="new_category" placeholder="Nhập tên danh mục mới..." required>
            <button type="submit" name="them_danhmuc" class="small-btn">Lưu</button>
        </form>
    </div>
    <form method="POST">
    <label>Danh mục thu nhập:</label>
    <div class="row-flex">
        <select name="category_id" required>
            <option value="">-- Chọn danh mục --</option>
            <?php 
            $categories = mysqli_query($conn, 
                "SELECT id, name FROM categories 
                WHERE user_id='$user_id' AND type='income'");
            while ($cat = mysqli_fetch_assoc($categories)): ?>
                <option value="<?php echo $cat['id']; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="button" class="small-btn" onclick="toggleAddCategory()">Thêm</button>
    </div>
    <div id="addCategoryForm" class="hidden-form">
        <input type="text" name="new_category" placeholder="Nhập tên danh mục mới...">
        <button type="submit" name="them_danhmuc" class="small-btn">Lưu</button>
    </div>

    <label>Số tiền (VNĐ):</label>
    <input type="number" name="amount" step="0.01" required>

    <label>Ngày thu:</label>
    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>">

    <label>Ghi chú:</label>
    <textarea name="note" rows="3"></textarea>

    <button type="submit" name="them_thunhap" class="submit-btn">Thêm thu nhập</button>
</form>


    <h3 style="margin-bottom:10px;">Danh sách thu nhập</h3>
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
            <td style="color:#27ae60; font-weight:bold;"><?php echo number_format($row['amount'], 0); ?></td>
            <td><?php echo htmlspecialchars($row['note']); ?></td>
            <td>
                <button class="action-btn edit-btn" onclick="toggleEditForm(<?php echo $row['id']; ?>)">Sửa</button>
                <a href="?xoa=<?php echo $row['id']; ?>" onclick="return confirm('Xóa khoản này?');">
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
                    <button type="submit" name="capnhat_thu" class="small-btn" style="background:#f1c40f;">Lưu thay đổi</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="thongke_thunhap.php" class="small-btn" >Thống kê thu nhập</a>

    <div class="back"><a href="index.php">← Quay lại trang chính</a></div>
</div>
</body>
</html>
