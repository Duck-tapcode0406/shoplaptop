<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vui lòng đăng nhập.';
    echo json_encode($response);
    exit();
}

// Check if wishlist table exists
$check_table = $conn->query("SHOW TABLES LIKE 'wishlist'");
if (!$check_table || $check_table->num_rows === 0) {
    $response['message'] = 'Hệ thống danh sách yêu thích chưa được kích hoạt.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $user_id = $_SESSION['user_id'];

    if ($product_id <= 0) {
        $response['message'] = 'Sản phẩm không hợp lệ.';
        echo json_encode($response);
        exit();
    }

    // Remove from wishlist
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Đã xóa khỏi danh sách yêu thích!';
    } else {
        $response['message'] = 'Có lỗi xảy ra: ' . $conn->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Phương thức không hợp lệ.';
}

echo json_encode($response);
$conn->close();
?>





