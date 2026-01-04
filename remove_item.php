<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';

// Require login
requireLogin();

// Kiểm tra xem có thông tin về sản phẩm cần xóa trong POST không
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['product_id'])) {
    // Validate CSRF
    validateCSRFPost();
    
    // Validate input
    $order_id = intval($_POST['order_id'] ?? 0);
    $product_id = intval($_POST['product_id'] ?? 0);
    $color_name = isset($_POST['color_name']) && !empty($_POST['color_name']) 
        ? Validator::sanitize($_POST['color_name']) 
        : null;
    $configuration_name = isset($_POST['configuration_name']) && !empty($_POST['configuration_name']) 
        ? Validator::sanitize($_POST['configuration_name']) 
        : null;

    // Verify order belongs to user
    $check_order = $conn->prepare("SELECT id FROM `order` WHERE id = ? AND customer_id = ?");
    $check_order->bind_param('ii', $order_id, $_SESSION['user_id']);
    $check_order->execute();
    $order_result = $check_order->get_result();
    
    if ($order_result->num_rows === 0) {
        redirect('cart.php?error=invalid_order');
    }

    // Xóa sản phẩm khỏi bảng order_details using prepared statement
    $delete_sql = "DELETE FROM order_details 
                   WHERE order_id = ? 
                   AND product_id = ? 
                   AND status = 'pending'";
    
    if ($color_name) {
        $delete_sql .= " AND (color_name = ? OR color_name IS NULL)";
    } else {
        $delete_sql .= " AND color_name IS NULL";
    }
    
    if ($configuration_name) {
        $delete_sql .= " AND (configuration_name = ? OR configuration_name IS NULL)";
    } else {
        $delete_sql .= " AND configuration_name IS NULL";
    }
    
    $delete_stmt = $conn->prepare($delete_sql);
    
    // Bind parameters dynamically
    if ($color_name && $configuration_name) {
        $delete_stmt->bind_param('iiss', $order_id, $product_id, $color_name, $configuration_name);
    } elseif ($color_name) {
        $delete_stmt->bind_param('iis', $order_id, $product_id, $color_name);
    } elseif ($configuration_name) {
        $delete_stmt->bind_param('iis', $order_id, $product_id, $configuration_name);
    } else {
        $delete_stmt->bind_param('ii', $order_id, $product_id);
    }
    
    if ($delete_stmt->execute()) {
        redirect('cart.php?success=removed');
    } else {
        error_log("Error removing item: " . $delete_stmt->error);
        redirect('cart.php?error=remove_failed');
    }
} else {
    redirect('cart.php');
}
?>
