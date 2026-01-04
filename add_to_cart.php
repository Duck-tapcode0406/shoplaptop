<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';
require_once 'includes/error_handler.php';

// Require login
requireLogin();

// Validate CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRFPost();
}

// Kiểm tra nếu có ID sản phẩm được gửi đến
if (isset($_POST['product_id'])) {
    // Validate product ID
    $product_validation = Validator::productId($_POST['product_id']);
    if (!$product_validation['valid']) {
        handleError($product_validation['message'], 400);
    }
    $product_id = $product_validation['value'];
    
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Sanitize color_name và configuration_name
    $color_name = isset($_POST['color_name']) && !empty($_POST['color_name']) 
        ? Validator::sanitize($_POST['color_name']) 
        : null;
    $configuration_name = isset($_POST['configuration_name']) && !empty($_POST['configuration_name']) 
        ? Validator::sanitize($_POST['configuration_name']) 
        : null;
    
    // Nếu có configuration dạng "color - config", tách ra
    if (isset($_POST['configuration']) && !empty($_POST['configuration'])) {
        $configuration = explode(' - ', $_POST['configuration']);
        $color_name = !empty($configuration[0]) ? Validator::sanitize($configuration[0]) : null;
        $configuration_name = !empty($configuration[1]) ? Validator::sanitize($configuration[1]) : null;
    }

    // Kiểm tra nếu khách hàng đã có đơn hàng đang xử lý
    if (!isset($_SESSION['order_id'])) {
        // Tạo mới một đơn hàng trong bảng `order` sử dụng prepared statement
        $customer_id = $_SESSION['user_id'];
        $datetime = date('Y-m-d H:i:s');
        
        $orderQuery = "INSERT INTO `order` (customer_id, datetime) VALUES (?, ?)";
        $stmt = $conn->prepare($orderQuery);
        $stmt->bind_param('is', $customer_id, $datetime);
        
        if ($stmt->execute()) {
            $_SESSION['order_id'] = $conn->insert_id; // Lưu lại ID đơn hàng vừa tạo
        } else {
            error_log("Error creating order: " . $conn->error);
            handleError("Lỗi khi tạo đơn hàng. Vui lòng thử lại.", 500);
        }
    }

    // Lưu thông tin chi tiết sản phẩm vào bảng `order_details`
    $order_id = $_SESSION['order_id'];

    // Truy vấn để lấy giá của sản phẩm mới nhất, kiểm tra nếu có giá tạm thời (giảm giá)
    $priceQuery = "SELECT price, temporary_price, discount_start, discount_end 
                   FROM price 
                   WHERE product_id = ? 
                   ORDER BY datetime DESC 
                   LIMIT 1";
    $price_stmt = $conn->prepare($priceQuery);
    $price_stmt->bind_param('i', $product_id);
    $price_stmt->execute();
    $priceResult = $price_stmt->get_result();

    if ($priceResult->num_rows > 0) {
        $price_data = $priceResult->fetch_assoc();
        $price = $price_data['price'];

        // Kiểm tra nếu có giá giảm (temporary_price)
        $current_time = date('Y-m-d H:i:s');
        if ($current_time >= $price_data['discount_start'] && $current_time <= $price_data['discount_end']) {
            $price = $price_data['temporary_price']; // Lấy giá tạm thời nếu đang trong thời gian giảm giá
        }

        // Kiểm tra xem sản phẩm đã có trong chi tiết đơn hàng chưa - sử dụng prepared statement
        $detailCheckQuery = "SELECT * FROM order_details 
                            WHERE order_id = ? 
                            AND product_id = ? 
                            AND status = 'pending'";
        
        // Build conditions for color_name and configuration_name
        if ($color_name) {
            $detailCheckQuery .= " AND (color_name = ? OR color_name IS NULL)";
        } else {
            $detailCheckQuery .= " AND color_name IS NULL";
        }
        
        if ($configuration_name) {
            $detailCheckQuery .= " AND (configuration_name = ? OR configuration_name IS NULL)";
        } else {
            $detailCheckQuery .= " AND configuration_name IS NULL";
        }
        
        $detailCheck_stmt = $conn->prepare($detailCheckQuery);
        
        // Bind parameters dynamically
        if ($color_name && $configuration_name) {
            $detailCheck_stmt->bind_param('iiss', $order_id, $product_id, $color_name, $configuration_name);
        } elseif ($color_name) {
            $detailCheck_stmt->bind_param('iis', $order_id, $product_id, $color_name);
        } elseif ($configuration_name) {
            $detailCheck_stmt->bind_param('iis', $order_id, $product_id, $configuration_name);
        } else {
            $detailCheck_stmt->bind_param('ii', $order_id, $product_id);
        }
        
        $detailCheck_stmt->execute();
        $detailCheckResult = $detailCheck_stmt->get_result();

        if ($detailCheckResult->num_rows > 0) {
            // Nếu sản phẩm đã tồn tại, cập nhật số lượng - sử dụng prepared statement
            $updateQuery = "UPDATE order_details 
                            SET quantity = quantity + ?, 
                                price = ? 
                            WHERE order_id = ? 
                            AND product_id = ? 
                            AND status = 'pending'";
            
            if ($color_name) {
                $updateQuery .= " AND (color_name = ? OR color_name IS NULL)";
            } else {
                $updateQuery .= " AND color_name IS NULL";
            }
            
            if ($configuration_name) {
                $updateQuery .= " AND (configuration_name = ? OR configuration_name IS NULL)";
            } else {
                $updateQuery .= " AND configuration_name IS NULL";
            }
            
            $update_stmt = $conn->prepare($updateQuery);
            
            // Bind parameters dynamically
            if ($color_name && $configuration_name) {
                $update_stmt->bind_param('diiiss', $quantity, $price, $order_id, $product_id, $color_name, $configuration_name);
            } elseif ($color_name) {
                $update_stmt->bind_param('diiis', $quantity, $price, $order_id, $product_id, $color_name);
            } elseif ($configuration_name) {
                $update_stmt->bind_param('diiis', $quantity, $price, $order_id, $product_id, $configuration_name);
            } else {
                $update_stmt->bind_param('diii', $quantity, $price, $order_id, $product_id);
            }
            
            $update_stmt->execute();
            
            if ($update_stmt->error) {
                error_log("Error updating order details: " . $update_stmt->error);
                handleError("Lỗi khi cập nhật giỏ hàng. Vui lòng thử lại.", 500);
            }
        } else {
            // Nếu sản phẩm chưa tồn tại, thêm mới vào bảng `order_details` - sử dụng prepared statement
            $insertQuery = "INSERT INTO order_details (order_id, product_id, quantity, price, color_name, configuration_name, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $insert_stmt = $conn->prepare($insertQuery);
            $insert_stmt->bind_param('iiidss', $order_id, $product_id, $quantity, $price, $color_name, $configuration_name);
            $insert_stmt->execute();
            
            if ($insert_stmt->error) {
                error_log("Error inserting order details: " . $insert_stmt->error);
                handleError("Lỗi khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.", 500);
            }
        }
    } else {
        handleError("Không tìm thấy giá sản phẩm.", 404);
    }
    
    // Chuyển hướng về trang sản phẩm (product_detail.php)
    redirect('product_detail.php?id=' . $product_id);
} else {
    handleError("Thiếu thông tin sản phẩm.", 400);
}
?>
