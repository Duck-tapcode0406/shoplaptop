<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
include '../includes/db.php'; // Kết nối với cơ sở dữ liệu

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kiểm tra quyền admin
$user_id = $_SESSION['user_id'];
$admin_check = $conn->prepare("SELECT is_admin FROM user WHERE id = ?");
$admin_check->bind_param('i', $user_id);
$admin_check->execute();
$admin_result = $admin_check->get_result();
$admin_data = $admin_result->fetch_assoc();

if (!$admin_data || $admin_data['is_admin'] != 1) {
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

// Kiểm tra dữ liệu gửi qua POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $supplier_id = intval($_POST['id']);

    // Xóa nhà cung cấp theo ID
    $sql_delete = "DELETE FROM supplier WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $supplier_id);

    if ($stmt->execute()) {
        // Chuyển hướng về trang quản lý nhà cung cấp sau khi xóa thành công
        header('Location: supplier.php?message=delete_success');
        exit;
    } else {
        echo "Có lỗi xảy ra khi xóa nhà cung cấp.";
    }

    $stmt->close();
} else {
    echo "Yêu cầu không hợp lệ.";
}

// Đóng kết nối cơ sở dữ liệu
$conn->close();
?>
