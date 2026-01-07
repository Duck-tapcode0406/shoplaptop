<?php
/**
 * API Endpoint để upload ảnh đại diện
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để sử dụng tính năng này'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Kiểm tra method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method không hợp lệ'
    ]);
    exit();
}

// Kiểm tra file upload
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'Không có file được upload hoặc có lỗi xảy ra'
    ]);
    exit();
}

$file = $_FILES['avatar'];

// Kiểm tra loại file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$file_type = mime_content_type($file['tmp_name']);

if (!in_array($file_type, $allowed_types)) {
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)'
    ]);
    exit();
}

// Kiểm tra kích thước file (5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    echo json_encode([
        'success' => false,
        'message' => 'Kích thước file không được vượt quá 5MB'
    ]);
    exit();
}

try {
    // Tạo thư mục upload nếu chưa có
    $upload_dir = __DIR__ . '/../uploads/avatars/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Lấy thông tin user hiện tại để xóa ảnh cũ
    $user_query = $conn->prepare("SELECT avatar FROM user WHERE id = ?");
    $user_query->bind_param('i', $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    $user = $user_result->fetch_assoc();
    $old_avatar = $user['avatar'] ?? '';

    // Tạo tên file mới
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Cập nhật database
        $update_stmt = $conn->prepare("UPDATE user SET avatar = ? WHERE id = ?");
        $update_stmt->bind_param('si', $new_filename, $user_id);
        
        if ($update_stmt->execute()) {
            // Xóa ảnh cũ nếu có (trừ ảnh mặc định)
            if (!empty($old_avatar) && $old_avatar !== 'avatar-default.png') {
                $old_path = $upload_dir . $old_avatar;
                if (file_exists($old_path)) {
                    @unlink($old_path);
                }
            }

            // Trả về đường dẫn ảnh mới
            $image_url = 'uploads/avatars/' . $new_filename;
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã cập nhật ảnh đại diện thành công!',
                'imageUrl' => $image_url
            ]);
        } else {
            // Xóa file vừa upload nếu cập nhật DB thất bại
            @unlink($target_path);
            echo json_encode([
                'success' => false,
                'message' => 'Không thể cập nhật ảnh đại diện trong database'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể upload file'
        ]);
    }
} catch (Exception $e) {
    error_log('Avatar upload error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}



