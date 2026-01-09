<?php
/**
 * API endpoint để cập nhật trạng thái đơn hàng
 * Sử dụng AJAX để cập nhật mà không reload trang
 */

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

// Check if admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập!']);
    exit();
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
$admin_check = $conn->prepare("SELECT is_admin FROM user WHERE id = ?");
$admin_check->bind_param('i', $user_id);
$admin_check->execute();
$admin_result = $admin_check->get_result();
$admin_data = $admin_result ? $admin_result->fetch_assoc() : null;

if (!$admin_data || $admin_data['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập!']);
    exit();
}

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Validate CSRF
$csrf_token = $_POST['csrf_token'] ?? '';
if (!validateCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token không hợp lệ!']);
    exit();
}

// Validate input
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

if ($order_id <= 0 || empty($new_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết!']);
    exit();
}

$allowed_status = ['pending', 'processing', 'shipped', 'paid', 'cancelled'];
if (!in_array($new_status, $allowed_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ!']);
    exit();
}

// Update status in database
try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Update order_details status
    $stmt = $conn->prepare("UPDATE order_details SET status = ? WHERE order_id = ?");
    $stmt->bind_param('si', $new_status, $order_id);
    
    if ($stmt->execute()) {
        // Commit transaction
        $conn->commit();
        // Get status text and class
        $status_text = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang giao',
            'paid' => 'Hoàn thành',
            'cancelled' => 'Đã hủy'
        ];
        
        $status_class = [
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger'
        ];
        
        // Get updated status counts
        $status_counts_query = $conn->query("
            SELECT od.status, COUNT(DISTINCT od.order_id) as cnt
            FROM order_details od
            GROUP BY od.status
        ");
        
        $status_counts = [];
        while ($row = $status_counts_query->fetch_assoc()) {
            $status_counts[$row['status']] = $row['cnt'];
        }
        
        // Calculate total orders
        $total_orders = array_sum($status_counts);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!',
            'data' => [
                'order_id' => $order_id,
                'new_status' => $new_status,
                'status_text' => $status_text[$new_status] ?? $new_status,
                'status_class' => $status_class[$new_status] ?? 'info',
                'status_counts' => $status_counts,
                'total_orders' => $total_orders
            ],
            // Also include at root level for easier access
            'status_text' => $status_text[$new_status] ?? $new_status,
            'status_class' => $status_class[$new_status] ?? 'info',
            'status_counts' => $status_counts
        ]);
    } else {
        // Rollback transaction
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái trong cơ sở dữ liệu!']);
    }
    $stmt->close();
} catch (Exception $e) {
    // Rollback transaction on error
    if (method_exists($conn, 'in_transaction') && $conn->in_transaction) {
        $conn->rollback();
    } else {
        // Try to rollback anyway
        try {
            $conn->rollback();
        } catch (Exception $rollbackError) {
            // Ignore rollback errors
        }
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

