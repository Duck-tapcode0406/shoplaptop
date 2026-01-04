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
$sql_check_admin = "SELECT r.name FROM role r 
                    JOIN user_role ur ON ur.role_id = r.id 
                    WHERE ur.user_id = '$user_id' AND r.name = 'admin'";
$result_check_admin = $conn->query($sql_check_admin);

if ($result_check_admin->num_rows == 0) {
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
