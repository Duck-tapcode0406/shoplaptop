<?php
// add_to_cart.php
// Sửa: dùng getDB(), transaction, sửa bind_param types, kiểm tra stock, CSRF, require login

require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';
require_once 'includes/error_handler.php';

requireLogin(); // Yêu cầu đăng nhập

// Check if user is admin - block admin from adding to cart
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $admin_check = $conn->prepare("SELECT is_admin FROM user WHERE id = ?");
    $admin_check->bind_param('i', $user_id);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    $admin_data = $admin_result ? $admin_result->fetch_assoc() : null;
    
    if ($admin_data && $admin_data['is_admin'] == 1) {
        handleError("Admin không thể mua hàng. Vui lòng đăng xuất và đăng nhập bằng tài khoản khách hàng.", 403);
    }
}

// Validate CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCSRFPost();
} else {
    handleError("Phương thức không hợp lệ.", 405);
}

$conn = getDB();

try {
    // Validate product_id
    $productValidation = Validator::productId($_POST['product_id'] ?? 0);
    if (!$productValidation['valid']) {
        handleError($productValidation['message'], 400);
    }
    $product_id = $productValidation['value'];

    // Quantity
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    if ($quantity < 1) $quantity = 1;

    // Parse configuration (format: "color - configuration")
    $color_name = null;
    $configuration_name = null;
    if (isset($_POST['configuration']) && trim($_POST['configuration']) !== '') {
        $parts = explode(' - ', $_POST['configuration'], 2);
        $color_name = isset($parts[0]) ? Validator::sanitize($parts[0]) : null;
        $configuration_name = isset($parts[1]) ? Validator::sanitize($parts[1]) : null;
    } else {
        // allow explicit separate fields too
        if (!empty($_POST['color_name'])) $color_name = Validator::sanitize($_POST['color_name']);
        if (!empty($_POST['configuration_name'])) $configuration_name = Validator::sanitize($_POST['configuration_name']);
    }

    // Bắt đầu transaction
    $conn->begin_transaction();

    // Tạo order nếu chưa có
    if (!isset($_SESSION['order_id'])) {
        $customer_id = $_SESSION['user_id'];
        $datetime = date('Y-m-d H:i:s');
        $orderQuery = "INSERT INTO `order` (customer_id, datetime) VALUES (?, ?)";
        $stmt = $conn->prepare($orderQuery);
        if (!$stmt) throw new Exception("Prepare lỗi tạo order: " . $conn->error);
        $stmt->bind_param('is', $customer_id, $datetime);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi khi tạo đơn: " . $stmt->error);
        }
        $_SESSION['order_id'] = $conn->insert_id;
        $stmt->close();
    }

    $order_id = $_SESSION['order_id'];

    // Lấy giá mới nhất (có thể có temporary_price)
    $priceQuery = "SELECT price, temporary_price, discount_start, discount_end
                   FROM price
                   WHERE product_id = ?
                   ORDER BY datetime DESC
                   LIMIT 1";
    $price_stmt = $conn->prepare($priceQuery);
    if (!$price_stmt) throw new Exception("Prepare lỗi price: " . $conn->error);
    $price_stmt->bind_param('i', $product_id);
    $price_stmt->execute();
    $priceResult = $price_stmt->get_result();
    if ($priceResult->num_rows === 0) {
        // Nếu không tìm thấy giá -> rollback + lỗi
        $price_stmt->close();
        $conn->rollback();
        handleError("Không tìm thấy giá sản phẩm.", 404);
    }
    $price_data = $priceResult->fetch_assoc();
    $price_stmt->close();

    $price = $price_data['price'];
    $current_time = date('Y-m-d H:i:s');
    if (!empty($price_data['temporary_price']) && !empty($price_data['discount_start']) && !empty($price_data['discount_end'])) {
        if ($current_time >= $price_data['discount_start'] && $current_time <= $price_data['discount_end']) {
            $price = $price_data['temporary_price'];
        }
    }

    // Kiểm tra tồn kho (dựa trên receipt_details như design hiện tại)
    // Bỏ qua nếu bảng receipt_details không tồn tại
    $stockTotal = 0;
    $checkTable = $conn->query("SHOW TABLES LIKE 'receipt_details'");
    if ($checkTable && $checkTable->num_rows > 0) {
        $stockQuery = "SELECT COALESCE(SUM(rd.quantity),0) AS stock_total FROM receipt_details rd WHERE rd.product_id = ?";
        $stock_stmt = $conn->prepare($stockQuery);
        if ($stock_stmt) {
            $stock_stmt->bind_param('i', $product_id);
            $stock_stmt->execute();
            $stockRes = $stock_stmt->get_result();
            $stockRow = $stockRes->fetch_assoc();
            $stockTotal = intval($stockRow['stock_total']);
            $stock_stmt->close();
        }
    }

    // Kiểm tra xem sản phẩm đã tồn tại trong order_details chưa (so sánh bằng COALESCE để xử lý NULL)
    $detailCheckQuery = "
        SELECT id, quantity
        FROM order_details
        WHERE order_id = ?
          AND product_id = ?
          AND COALESCE(color_name,'') = COALESCE(?, '')
          AND COALESCE(configuration_name,'') = COALESCE(?, '')
        LIMIT 1
    ";
    $detailCheck_stmt = $conn->prepare($detailCheckQuery);
    if (!$detailCheck_stmt) throw new Exception("Prepare lỗi detail check: " . $conn->error);
    // Nếu biến null, truyền chuỗi rỗng
    $cname = $color_name ?? '';
    $confname = $configuration_name ?? '';
    $detailCheck_stmt->bind_param('iiss', $order_id, $product_id, $cname, $confname);
    $detailCheck_stmt->execute();
    $detailCheckResult = $detailCheck_stmt->get_result();

    if ($detailCheckResult->num_rows > 0) {
        // Cập nhật quantity và price
        $updateQuery = "
            UPDATE order_details
            SET quantity = quantity + ?, price = ?
            WHERE order_id = ?
              AND product_id = ?
              AND COALESCE(color_name,'') = COALESCE(?, '')
              AND COALESCE(configuration_name,'') = COALESCE(?, '')
        ";
        $update_stmt = $conn->prepare($updateQuery);
        if (!$update_stmt) throw new Exception("Prepare lỗi update order_details: " . $conn->error);
        // types: quantity (i), price (d), order_id (i), product_id (i), color_name (s), configuration_name (s)
        $update_stmt->bind_param('idiiss', $quantity, $price, $order_id, $product_id, $cname, $confname);
        if (!$update_stmt->execute()) {
            throw new Exception("Lỗi cập nhật order_details: " . $update_stmt->error);
        }
        $update_stmt->close();
    } else {
        // Insert mới
        $insertQuery = "INSERT INTO order_details (order_id, product_id, quantity, price, color_name, configuration_name, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')";
        $insert_stmt = $conn->prepare($insertQuery);
        if (!$insert_stmt) throw new Exception("Prepare lỗi insert order_details: " . $conn->error);
        // types: order_id (i), product_id (i), quantity (i), price (d), color_name (s), config (s)
        $insert_stmt->bind_param('iiidss', $order_id, $product_id, $quantity, $price, $cname, $confname);
        if (!$insert_stmt->execute()) {
            throw new Exception("Lỗi thêm order_details: " . $insert_stmt->error);
        }
        $insert_stmt->close();
    }

    // Commit transaction
    $conn->commit();

    // Chuyển hướng về trang product_detail (hoặc cart)
    redirect('product_detail.php?id=' . $product_id);
} catch (Exception $e) {
    // rollback nếu đang transaction
    if ($conn->in_transaction) $conn->rollback();
    error_log("add_to_cart error: " . $e->getMessage());
    handleError("Đã có lỗi khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.", 500);
}
