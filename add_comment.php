<?php
// add_comment.php
// Sửa: dùng getDB(), validate CSRF, require login, prepared statement

require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';
require_once 'includes/error_handler.php';

requireLogin(); // Yêu cầu đăng nhập

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Validate CSRF token
validateCSRFPost();

$conn = getDB();

// Lấy dữ liệu
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$comment_content = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($product_id <= 0 || $comment_content === '') {
    handleError("Dữ liệu không hợp lệ.", 400);
}

// Optional: giới hạn độ dài comment
if (mb_strlen($comment_content) > 2000) {
    handleError("Bình luận quá dài.", 400);
}

$user_id = $_SESSION['user_id'];
$created_at = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO comment (product_id, user_id, content, created_at) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    error_log("Prepare lỗi add_comment: " . $conn->error);
    handleError("Lỗi hệ thống. Vui lòng thử lại.", 500);
}
$stmt->bind_param('iiss', $product_id, $user_id, $comment_content, $created_at);
if ($stmt->execute()) {
    $stmt->close();
    // Quay lại trang chi tiết sản phẩm
    redirect('product_detail.php?id=' . $product_id);
} else {
    error_log("Lỗi khi thêm bình luận: " . $stmt->error);
    $stmt->close();
    handleError("Lỗi khi thêm bình luận. Vui lòng thử lại.", 500);
}
