<?php
/**
 * API để lưu địa điểm người dùng chọn
 */
require_once __DIR__ . '/../includes/session.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['location'])) {
    $_SESSION['user_location'] = $input['location'];
    echo json_encode(['success' => true, 'location' => $input['location']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
}
?>




