<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    validateCSRFPost();
    
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $color_name = isset($_POST['color_name']) && !empty($_POST['color_name']) 
        ? Validator::sanitize($_POST['color_name']) 
        : null;
    $configuration_name = isset($_POST['configuration_name']) && !empty($_POST['configuration_name']) 
        ? Validator::sanitize($_POST['configuration_name']) 
        : null;

    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Số lượng phải lớn hơn 0']);
        exit();
    }

    // Verify order belongs to user using prepared statement
    $check_order = $conn->prepare("SELECT id FROM `order` WHERE id = ? AND customer_id = ?");
    $check_order->bind_param('ii', $order_id, $user_id);
    $check_order->execute();
    $order_result = $check_order->get_result();
    
    if ($order_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không hợp lệ']);
        exit();
    }

    // Update quantity using prepared statement
    $update_sql = "UPDATE order_details 
                   SET quantity = ? 
                   WHERE order_id = ? 
                   AND product_id = ? 
                   AND status = 'pending'";
    
    if ($color_name) {
        $update_sql .= " AND (color_name = ? OR color_name IS NULL)";
    } else {
        $update_sql .= " AND color_name IS NULL";
    }
    
    if ($configuration_name) {
        $update_sql .= " AND (configuration_name = ? OR configuration_name IS NULL)";
    } else {
        $update_sql .= " AND configuration_name IS NULL";
    }

    $update_stmt = $conn->prepare($update_sql);
    
    // Bind parameters dynamically
    if ($color_name && $configuration_name) {
        $update_stmt->bind_param('iiiss', $quantity, $order_id, $product_id, $color_name, $configuration_name);
    } elseif ($color_name) {
        $update_stmt->bind_param('iiis', $quantity, $order_id, $product_id, $color_name);
    } elseif ($configuration_name) {
        $update_stmt->bind_param('iiis', $quantity, $order_id, $product_id, $configuration_name);
    } else {
        $update_stmt->bind_param('iii', $quantity, $order_id, $product_id);
    }
    
    if ($update_stmt->execute()) {
        // Get updated total using prepared statement
        $total_sql = "SELECT SUM(od.quantity * od.price) AS total
                     FROM order_details od
                     JOIN `order` o ON od.order_id = o.id
                     WHERE o.customer_id = ? AND od.status = 'pending'";
        $total_stmt = $conn->prepare($total_sql);
        $total_stmt->bind_param('i', $user_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total = $total_result->fetch_assoc()['total'] ?? 0;

        // Get item total using prepared statement
        $item_sql = "SELECT quantity * price AS item_total 
                    FROM order_details 
                    WHERE order_id = ? 
                    AND product_id = ? 
                    AND status = 'pending'";
        if ($color_name) {
            $item_sql .= " AND (color_name = ? OR color_name IS NULL)";
        } else {
            $item_sql .= " AND color_name IS NULL";
        }
        if ($configuration_name) {
            $item_sql .= " AND (configuration_name = ? OR configuration_name IS NULL)";
        } else {
            $item_sql .= " AND configuration_name IS NULL";
        }
        
        $item_stmt = $conn->prepare($item_sql);
        
        // Bind parameters dynamically
        if ($color_name && $configuration_name) {
            $item_stmt->bind_param('iiss', $order_id, $product_id, $color_name, $configuration_name);
        } elseif ($color_name) {
            $item_stmt->bind_param('iis', $order_id, $product_id, $color_name);
        } elseif ($configuration_name) {
            $item_stmt->bind_param('iis', $order_id, $product_id, $configuration_name);
        } else {
            $item_stmt->bind_param('ii', $order_id, $product_id);
        }
        
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        $item_total = $item_result->fetch_assoc()['item_total'] ?? 0;

        echo json_encode([
            'success' => true,
            'message' => 'Đã cập nhật số lượng',
            'item_total' => $item_total,
            'cart_total' => $total
        ]);
    } else {
        error_log("Error updating cart quantity: " . $update_stmt->error);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật. Vui lòng thử lại.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
