<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/error_handler.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    validateCSRFPost();
    
    try {
        // Handle shipping form submission (Step 2)
        if (isset($_POST['shipping_name']) && !isset($_POST['checkout_all'])) {
            // Validate shipping data
            $shipping_name = trim($_POST['shipping_name'] ?? '');
            $shipping_phone = trim($_POST['shipping_phone'] ?? '');
            $shipping_email = trim($_POST['shipping_email'] ?? '');
            $shipping_address = trim($_POST['shipping_address'] ?? '');
            $shipping_city = trim($_POST['shipping_city'] ?? '');
            $shipping_postal = trim($_POST['shipping_postal'] ?? '');
            
            // Validation
            if (empty($shipping_name) || strlen($shipping_name) < 2) {
                $error_message = "Họ và tên phải có ít nhất 2 ký tự!";
            } elseif (empty($shipping_phone) || !preg_match('/^[0-9]{10,11}$/', $shipping_phone)) {
                $error_message = "Số điện thoại không hợp lệ! (10-11 chữ số)";
            } elseif (empty($shipping_email) || !filter_var($shipping_email, FILTER_VALIDATE_EMAIL)) {
                $error_message = "Email không hợp lệ!";
            } elseif (empty($shipping_address) || strlen($shipping_address) < 10) {
                $error_message = "Địa chỉ phải có ít nhất 10 ký tự!";
            } elseif (empty($shipping_city) || strlen($shipping_city) < 2) {
                $error_message = "Thành phố/Tỉnh không hợp lệ!";
            } else {
                // Save to session
                $_SESSION['shipping_name'] = $shipping_name;
                $_SESSION['shipping_phone'] = $shipping_phone;
                $_SESSION['shipping_email'] = $shipping_email;
                $_SESSION['shipping_address'] = $shipping_address;
                $_SESSION['shipping_city'] = $shipping_city;
                $_SESSION['shipping_postal'] = $shipping_postal;
                
                // Redirect to payment step
                header('Location: checkout.php?step=3');
                exit();
            }
        }
        
        $conn->begin_transaction();

        if (isset($_POST['checkout_all'])) {
            // Use prepared statement to prevent SQL injection
            $order_check_sql = "SELECT o.id AS order_id, od.product_id, od.quantity, c.id AS color_id, c.color_name
                                FROM `order` o
                                JOIN `order_details` od ON o.id = od.order_id
                                JOIN `colors_configuration` c ON c.product_id = od.product_id
                                WHERE o.customer_id = ? AND od.status = 'pending'";
            $stmt = $conn->prepare($order_check_sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $order_check_result = $stmt->get_result();

            if ($order_check_result && $order_check_result->num_rows > 0) {
                while ($row = $order_check_result->fetch_assoc()) {
                    $order_id = $row['order_id'];
                    $product_id = $row['product_id'];
                    $quantity = $row['quantity'];
                    $color_id = $row['color_id'];

                    // Use prepared statements for updates
                    $update_quantity_stmt = $conn->prepare("UPDATE receipt_details SET quantity = quantity - ? WHERE product_id = ?");
                    $update_quantity_stmt->bind_param('ii', $quantity, $product_id);
                    $update_quantity_stmt->execute();

                    $update_color_quantity_stmt = $conn->prepare("UPDATE colors_configuration SET quantity = quantity - ? WHERE product_id = ? AND id = ?");
                    $update_color_quantity_stmt->bind_param('iii', $quantity, $product_id, $color_id);
                    $update_color_quantity_stmt->execute();

                    $update_status_stmt = $conn->prepare("UPDATE order_details SET status = 'paid' WHERE order_id = ? AND product_id = ?");
                    $update_status_stmt->bind_param('ii', $order_id, $product_id);
                    $update_status_stmt->execute();

                    if ($update_quantity_stmt->error || $update_color_quantity_stmt->error || $update_status_stmt->error) {
                        throw new Exception("Lỗi xử lý thanh toán: " . $conn->error);
                    }
                }
                $conn->commit();
                $success_message = "Thanh toán thành công! Đơn hàng của bạn đã được xác nhận.";
                $current_step = 4;
            } else {
                $error_message = "Không có sản phẩm nào trong giỏ hàng để thanh toán.";
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Checkout error: " . $e->getMessage());
        $error_message = "Lỗi: " . (DEBUG_MODE ? $e->getMessage() : "Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.");
    }
}
?>
<?php include('includes/header.php'); ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - DNQDH Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* VNPay Style Checkout */
        .checkout-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Header Banner */
        .checkout-header {
            background: linear-gradient(135deg, #0066b3 0%, #00a0e9 50%, #6C5CE7 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 40px rgba(0, 102, 179, 0.3);
            position: relative;
            overflow: hidden;
        }

        .checkout-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .checkout-header-info h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .checkout-header-info p {
            opacity: 0.9;
            font-size: 15px;
        }

        .checkout-header-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            z-index: 1;
        }

        .checkout-header-logo img {
            height: 50px;
            filter: brightness(0) invert(1);
        }

        /* Progress Steps - VNPay Style */
        .progress-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            position: relative;
            padding: 0 40px;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 15%;
            right: 15%;
            height: 3px;
            background: linear-gradient(90deg, #e0e0e0 0%, #e0e0e0 100%);
            z-index: 0;
            border-radius: 3px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            z-index: 2;
            max-width: 150px;
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 12px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            color: #999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #0066b3, #00a0e9);
            border-color: #0066b3;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 102, 179, 0.4);
        }

        .step.completed .step-number {
            background: linear-gradient(135deg, #00b894, #00cec9);
            border-color: #00b894;
            color: white;
            box-shadow: 0 8px 25px rgba(0, 184, 148, 0.4);
        }

        .step-name {
            font-size: 13px;
            font-weight: 600;
            color: #999;
            text-align: center;
            transition: color 0.3s;
        }

        .step.active .step-name {
            color: #0066b3;
        }

        .step.completed .step-name {
            color: #00b894;
        }

        /* Main Content Card */
        .checkout-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.08);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .checkout-section {
            margin-bottom: 35px;
        }

        .checkout-section h2 {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            font-size: 22px;
            color: #2d3436;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .checkout-section h2 i {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #0066b3, #00a0e9);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        /* Form Styles */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2d3436;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s;
            background: #fafbfc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0066b3;
            background: white;
            box-shadow: 0 0 0 4px rgba(0, 102, 179, 0.1);
        }

        .form-group input:invalid:not(:placeholder-shown),
        .form-group select:invalid:not(:placeholder-shown),
        .form-group textarea:invalid:not(:placeholder-shown) {
            border-color: #e74c3c;
            background: #fff5f5;
        }

        .form-group input:valid:not(:placeholder-shown),
        .form-group select:valid:not(:placeholder-shown),
        .form-group textarea:valid:not(:placeholder-shown) {
            border-color: #00b894;
        }

        .field-error {
            display: block;
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            min-height: 18px;
        }

        .form-group label span {
            margin-left: 4px;
        }

        /* Payment Methods - VNPay Style (Improved) */
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }

        .payment-option {
            border: 3px solid #e9ecef;
            border-radius: 20px;
            padding: 25px 30px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .payment-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: transparent;
            transition: all 0.4s;
        }

        .payment-option::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(0, 102, 179, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .payment-option:hover {
            border-color: #0066b3;
            background: linear-gradient(135deg, #ffffff, #f0f7ff);
            transform: translateX(8px) translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 102, 179, 0.2);
        }

        .payment-option:hover::after {
            opacity: 1;
        }

        .payment-option.selected {
            border-color: #0066b3;
            background: linear-gradient(135deg, rgba(0, 102, 179, 0.08), rgba(0, 160, 233, 0.08));
            box-shadow: 0 12px 40px rgba(0, 102, 179, 0.25);
            transform: translateX(5px);
        }

        .payment-option.selected::before {
            background: linear-gradient(180deg, #0066b3, #00a0e9);
            box-shadow: 0 0 20px rgba(0, 102, 179, 0.5);
        }

        .payment-option.selected::after {
            opacity: 1;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-radio {
            width: 24px;
            height: 24px;
            border: 2px solid #ccc;
            border-radius: 50%;
            margin-right: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .payment-option.selected .payment-radio {
            border-color: #0066b3;
            background: #0066b3;
        }

        .payment-option.selected .payment-radio::after {
            content: '✓';
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .payment-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.4s;
        }

        .payment-option:hover .payment-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .payment-icon.cod {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
        }

        .payment-icon.vnpay {
            background: linear-gradient(135deg, #0066b3, #00a0e9);
            padding: 12px;
        }

        .payment-icon.vnpay img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .payment-info {
            flex: 1;
        }

        .payment-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .payment-info strong {
            display: block;
            margin-bottom: 8px;
            color: #2d3436;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .payment-info small {
            color: #636e72;
            font-size: 14px;
            line-height: 1.5;
            display: block;
        }

        .payment-badge {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            font-size: 12px;
            padding: 6px 14px;
            border-radius: 25px;
            font-weight: 700;
            margin-left: auto;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
            position: relative;
            z-index: 1;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }


        /* Order Summary */
        .order-summary {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 16px;
            padding: 25px;
            margin-top: 25px;
        }

        .order-summary-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }

        .order-summary-header i {
            color: #0066b3;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px dashed #ddd;
        }

        .order-item:last-of-type {
            border-bottom: none;
        }

        .order-item-name {
            font-weight: 500;
            color: #2d3436;
        }

        .order-item-price {
            font-weight: 600;
            color: #636e72;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0 0;
            margin-top: 15px;
            border-top: 2px solid #0066b3;
        }

        .order-total span:first-child {
            font-size: 18px;
            font-weight: 600;
            color: #2d3436;
        }

        .order-total span:last-child {
            font-size: 26px;
            font-weight: 800;
            background: linear-gradient(135deg, #0066b3, #00a0e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Checkout Actions */
        .checkout-actions {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .checkout-actions .btn {
            padding: 16px 35px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkout-actions .btn-secondary {
            background: #f0f0f0;
            color: #636e72;
            border: none;
        }

        .checkout-actions .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateX(-3px);
        }

        .checkout-actions .btn-primary {
            background: linear-gradient(135deg, #0066b3, #00a0e9);
            color: white;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 102, 179, 0.3);
        }

        .checkout-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 102, 179, 0.4);
        }

        /* Messages */
        .success-message {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 5px solid #00b894;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease-out;
        }

        .success-message i {
            color: #00b894;
            font-size: 28px;
        }

        .error-message {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 5px solid #e74c3c;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease-out;
        }

        .error-message i {
            color: #e74c3c;
            font-size: 28px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Trust Badges */
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .trust-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #636e72;
            font-size: 13px;
        }

        .trust-badge i {
            font-size: 20px;
            color: #00b894;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .checkout-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .checkout-actions {
                flex-direction: column;
            }

            .checkout-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .trust-badges {
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }

            .progress-steps {
                padding: 0 10px;
            }

            .step-name {
                font-size: 11px;
            }

            .step-number {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <!-- Back Button -->
        <?php if ($current_step > 1): ?>
        <a href="<?php echo $current_step == 2 ? 'cart.php' : 'checkout.php?step=' . ($current_step - 1); ?>" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
        <?php endif; ?>
        
        <!-- Checkout Header Banner -->
        <div class="checkout-header">
            <div class="checkout-header-info">
                <h1><i class="fas fa-shield-alt"></i> Thanh Toán An Toàn</h1>
                <p>Giao dịch được bảo mật với tiêu chuẩn quốc tế</p>
            </div>
            <div class="checkout-header-logo">
                <img src="images/logo.png" alt="DNQDH Shop">
                <span style="font-size: 20px; font-weight: bold;">DNQDH Shop</span>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step <?php echo $current_step >= 1 ? 'completed' : ''; ?> <?php echo $current_step === 1 ? 'active' : ''; ?>">
                <div class="step-number"><?php echo $current_step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?></div>
                <div class="step-name">Giỏ Hàng</div>
            </div>
            <div class="step <?php echo $current_step >= 2 ? 'completed' : ''; ?> <?php echo $current_step === 2 ? 'active' : ''; ?>">
                <div class="step-number"><?php echo $current_step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?></div>
                <div class="step-name">Vận Chuyển</div>
            </div>
            <div class="step <?php echo $current_step >= 3 ? 'completed' : ''; ?> <?php echo $current_step === 3 ? 'active' : ''; ?>">
                <div class="step-number"><?php echo $current_step > 3 ? '<i class="fas fa-check"></i>' : '3'; ?></div>
                <div class="step-name">Thanh Toán</div>
            </div>
            <div class="step <?php echo $current_step >= 4 ? 'completed' : ''; ?> <?php echo $current_step === 4 ? 'active' : ''; ?>">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-name">Xác Nhận</div>
            </div>
        </div>

        <div class="checkout-content">
            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo $success_message; ?></div>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?php echo $error_message; ?></div>
                </div>
            <?php endif; ?>

            <!-- Step 1: Cart Review -->
            <?php if ($current_step === 1): ?>
                <div class="checkout-section">
                    <h2><i class="fas fa-shopping-bag"></i> Kiểm Tra Giỏ Hàng</h2>
                    <div class="order-summary">
                        <?php
                        $cart_sql = "SELECT o.id AS order_id, od.product_id, od.quantity, od.price, p.name, od.color_name
                                    FROM `order` o
                                    JOIN `order_details` od ON o.id = od.order_id
                                    JOIN `product` p ON od.product_id = p.id
                                    WHERE o.customer_id = $user_id AND od.status = 'pending'";
                        $cart_result = $conn->query($cart_sql);
                        $total = 0;
                        
                        if ($cart_result && $cart_result->num_rows > 0):
                            while ($item = $cart_result->fetch_assoc()):
                                $item_total = $item['quantity'] * $item['price'];
                                $total += $item_total;
                        ?>
                            <div class="order-item">
                                <div>
                                    <div style="font-weight: var(--fw-bold);"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div style="font-size: var(--fs-small); color: var(--text-secondary);">
                                        Số lượng: <strong><?php echo $item['quantity']; ?></strong>
                                        <?php if ($item['color_name']): ?> | Màu: <strong><?php echo htmlspecialchars($item['color_name']); ?></strong><?php endif; ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div><?php echo number_format($item['price'], 0, ',', '.'); ?> ₫ x <?php echo $item['quantity']; ?></div>
                                    <div style="font-weight: var(--fw-bold); color: var(--primary);"><?php echo number_format($item_total, 0, ',', '.'); ?> ₫</div>
                                </div>
                            </div>
                        <?php endwhile; endif; ?>
                        <div class="order-total">
                            <span>Tổng Cộng:</span>
                            <span><?php echo number_format($total, 0, ',', '.'); ?> ₫</span>
                        </div>
                    </div>
                    
                    <div class="checkout-actions">
                        <a href="cart.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại Giỏ Hàng</a>
                        <a href="checkout.php?step=2" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Tiếp Tục Đến Vận Chuyển</a>
                    </div>
                </div>

            <!-- Step 2: Shipping -->
            <?php elseif ($current_step === 2): ?>
                <div class="checkout-section">
                    <h2><i class="fas fa-truck"></i> Địa Chỉ Vận Chuyển</h2>
                    <div style="margin-bottom: var(--space-lg); padding: var(--space-md); background: #f8f9fa; border-radius: var(--radius-md); border-left: 4px solid var(--primary);">
                        <p style="margin: 0 0 var(--space-sm) 0; font-weight: var(--fw-bold);">
                            <i class="fas fa-map-marker-alt"></i> Xác Nhận Vị Trí Tự Động
                        </p>
                        <p style="margin: 0; font-size: var(--fs-small); color: var(--text-secondary);">
                            Cho phép trình duyệt truy cập vị trí để tự động điền thông tin địa chỉ giao hàng
                        </p>
                        <button type="button" id="verify-location-btn" class="btn btn-primary" style="margin-top: var(--space-sm);">
                            <i class="fas fa-crosshairs"></i> Xác Nhận Vị Trí Hiện Tại
                        </button>
                    </div>
                    <form method="POST" id="shipping-form" onsubmit="return validateShippingForm(event)">
                        <?php echo getCSRFTokenField(); ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Họ và Tên <span style="color: #e74c3c;">*</span></label>
                                <input type="text" name="shipping_name" id="shipping_name" required placeholder="Nhập họ và tên" minlength="2">
                                <small class="field-error" id="shipping_name_error"></small>
                            </div>
                            <div class="form-group">
                                <label>Số Điện Thoại <span style="color: #e74c3c;">*</span></label>
                                <input type="tel" name="shipping_phone" id="shipping_phone" required placeholder="Nhập số điện thoại" pattern="[0-9]{10,11}">
                                <small class="field-error" id="shipping_phone_error"></small>
                            </div>
                        </div>
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Email <span style="color: #e74c3c;">*</span></label>
                                <input type="email" name="shipping_email" id="shipping_email" required placeholder="Nhập email để nhận thông báo đơn hàng">
                                <small class="field-error" id="shipping_email_error"></small>
                            </div>
                        </div>
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Địa Chỉ <span style="color: #e74c3c;">*</span></label>
                                <input type="text" name="shipping_address" id="shipping_address" required placeholder="Nhập địa chỉ giao hàng" minlength="10">
                                <small class="field-error" id="shipping_address_error"></small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Thành Phố/Tỉnh <span style="color: #e74c3c;">*</span></label>
                                <input type="text" name="shipping_city" id="shipping_city" required placeholder="Nhập thành phố/tỉnh" minlength="2">
                                <small class="field-error" id="shipping_city_error"></small>
                            </div>
                            <div class="form-group">
                                <label>Mã Bưu Chính</label>
                                <input type="text" name="shipping_postal" id="shipping_postal" placeholder="Nhập mã bưu chính (tùy chọn)" pattern="[0-9]{5,6}">
                                <small class="field-error" id="shipping_postal_error"></small>
                            </div>
                        </div>
                        <div class="checkout-actions">
                            <a href="cart.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
                            <button type="submit" class="btn btn-primary" id="continue-to-payment-btn">
                                <i class="fas fa-arrow-right"></i> Tiếp Tục Đến Thanh Toán
                            </button>
                        </div>
                    </form>
                </div>

            <!-- Step 3: Payment -->
            <?php elseif ($current_step === 3): ?>
                <div class="checkout-section">
                    <h2><i class="fas fa-credit-card"></i> Phương Thức Thanh Toán</h2>
                    <h5 style="margin-bottom: 15px; color: #636e72; font-weight: 500;">
                        <i class="fas fa-hand-pointer" style="margin-right: 8px;"></i>Chọn phương thức thanh toán phù hợp với bạn:
                    </h5>
                    <div class="payment-methods">
                        <label class="payment-option selected" id="payment-cod">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div class="payment-radio"></div>
                            <div class="payment-icon cod"><i class="fas fa-money-bill-wave"></i></div>
                            <div class="payment-info">
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <small>Thanh toán bằng tiền mặt khi shipper giao hàng đến tận nơi</small>
                            </div>
                        </label>
                        <label class="payment-option" id="payment-vnpay">
                            <input type="radio" name="payment_method" value="vnpay">
                            <div class="payment-radio"></div>
                            <div class="payment-icon vnpay">
                                <img src="https://sandbox.vnpayment.vn/paymentv2/images/logo.svg" alt="VNPay" onerror="this.parentElement.innerHTML='<i class=\'fas fa-university\' style=\'color:white;font-size:24px;\'></i>'">
                            </div>
                            <div class="payment-info">
                                <strong>VNPay - Thanh toán trực tuyến</strong>
                                <small>ATM nội địa, Visa, MasterCard, JCB, QR Code - Nhanh chóng & An toàn</small>
                            </div>
                            <span class="payment-badge">Khuyên dùng</span>
                        </label>
                    </div>

                    <div class="order-summary" style="margin-top: var(--space-2xl);">
                        <?php
                        $cart_sql = "SELECT od.product_id, od.quantity, od.price, p.name
                                    FROM `order` o
                                    JOIN `order_details` od ON o.id = od.order_id
                                    JOIN `product` p ON od.product_id = p.id
                                    WHERE o.customer_id = ? AND od.status = 'pending'";
                        $cart_stmt = $conn->prepare($cart_sql);
                        $cart_stmt->bind_param('i', $user_id);
                        $cart_stmt->execute();
                        $cart_result = $cart_stmt->get_result();
                        $subtotal = 0;
                        
                        if ($cart_result && $cart_result->num_rows > 0):
                            while ($item = $cart_result->fetch_assoc()):
                                $item_total = $item['quantity'] * $item['price'];
                                $subtotal += $item_total;
                        ?>
                            <div class="order-item">
                                <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                                <span><?php echo number_format($item_total, 0, ',', '.'); ?> ₫</span>
                            </div>
                        <?php endwhile; endif; 
                        $shipping = 0;
                        $tax = round($subtotal * 0.1);
                        $total = $subtotal + $shipping + $tax;
                        ?>
                        <div class="order-item">
                            <span>Vận Chuyển</span>
                            <span><?php echo number_format($shipping, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <div class="order-item">
                            <span>Thuế VAT (10%)</span>
                            <span><?php echo number_format($tax, 0, ',', '.'); ?> ₫</span>
                        </div>
                        <div class="order-total">
                            <span>Tổng Cộng</span>
                            <span id="total-amount"><?php echo number_format($total, 0, ',', '.'); ?> ₫</span>
                        </div>
                    </div>

                    <!-- COD Form -->
                    <form method="POST" id="cod-form" class="checkout-actions" style="margin-top: var(--space-2xl);">
                        <?php echo getCSRFTokenField(); ?>
                        <a href="checkout.php?step=2" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
                        <button type="submit" name="checkout_all" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i> Xác Nhận Thanh Toán COD
                        </button>
                    </form>

                    <!-- VNPay Form (Hidden - Auto submit when VNPay is selected) -->
                    <form method="POST" action="vnpay_create_payment.php" id="vnpay-form" style="display: none;">
                        <?php echo getCSRFTokenField(); ?>
                        <input type="hidden" name="bank_code" id="vnpay_bank_code" value="">
                        <input type="hidden" name="shipping_name" value="<?php echo htmlspecialchars($_SESSION['shipping_name'] ?? ''); ?>">
                        <input type="hidden" name="shipping_phone" value="<?php echo htmlspecialchars($_SESSION['shipping_phone'] ?? ''); ?>">
                        <input type="hidden" name="shipping_email" value="<?php echo htmlspecialchars($_SESSION['shipping_email'] ?? ''); ?>">
                        <input type="hidden" name="shipping_address" value="<?php echo htmlspecialchars($_SESSION['shipping_address'] ?? ''); ?>">
                        <input type="hidden" name="shipping_city" value="<?php echo htmlspecialchars($_SESSION['shipping_city'] ?? ''); ?>">
                    </form>
                </div>

            <!-- Step 4: Confirmation -->
            <?php elseif ($current_step === 4): ?>
                <div class="checkout-section" style="text-align: center;">
                    <div style="font-size: 4rem; color: var(--success); margin-bottom: var(--space-lg);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 style="color: var(--success); margin-bottom: var(--space-lg);">Đơn Hàng Đã Được Xác Nhận!</h2>
                    <p style="color: var(--text-secondary); margin-bottom: var(--space-2xl); font-size: var(--fs-large);">
                        Cảm ơn bạn đã mua hàng. Chúng tôi sẽ sớm giao hàng đến bạn.
                    </p>
                    <div class="order-summary">
                        <p style="text-align: left; margin-bottom: var(--space-lg);">Thông tin đơn hàng:</p>
                        <div class="order-item">
                            <span>Trạng Thái</span>
                            <span><span class="badge" style="background-color: var(--success); color: white; padding: 4px 12px; border-radius: 999px;">Đã Thanh Toán</span></span>
                        </div>
                        <div class="order-item">
                            <span>Dự Kiến Giao Hàng</span>
                            <span><?php echo date('d/m/Y', strtotime('+3 days')); ?></span>
                        </div>
                    </div>
                    <div class="checkout-actions">
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-shopping-bag"></i> Tiếp Tục Mua Sắm</a>
                        <a href="history.php" class="btn btn-primary"><i class="fas fa-receipt"></i> Xem Lịch Sử Đơn Hàng</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Trust Badges -->
        <div class="trust-badges">
            <div class="trust-badge">
                <i class="fas fa-lock"></i>
                <span>Bảo mật SSL 256-bit</span>
            </div>
            <div class="trust-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Thanh toán an toàn</span>
            </div>
            <div class="trust-badge">
                <i class="fas fa-undo"></i>
                <span>Hoàn tiền trong 7 ngày</span>
            </div>
            <div class="trust-badge">
                <i class="fas fa-headset"></i>
                <span>Hỗ trợ 24/7</span>
            </div>
        </div>
    </div>

    <!-- Location verification script already loaded via header.php -->
    <script>
        // Đợi DOM ready trước khi khởi tạo
        document.addEventListener('DOMContentLoaded', function() {
            // Kiểm tra xem LocationVerification đã được load chưa
            if (typeof LocationVerification === 'undefined') {
                console.error('LocationVerification class not loaded. Make sure js/location-verification.js is included.');
                return;
            }

            // Khởi tạo Location Verification
            window.locationVerificationInstance = new LocationVerification({
                apiEndpoint: 'api/update_location.php',
                onSuccess: function(data) {
                    console.log('Đã cập nhật địa chỉ:', data);
                },
                onError: function(message) {
                    console.error('Lỗi:', message);
                    // Hiển thị thông báo lỗi cho user
                    if (typeof window.showError === 'function') {
                        window.showError('Lỗi', message);
                    } else {
                        alert('Lỗi: ' + message);
                    }
                },
                onLocationFound: function(place, location) {
                    // Callback này chỉ để log, không điền form ở đây
                    // Form sẽ được điền trong confirmLocation sau khi user xác nhận
                    console.log('Location found:', place, location);
                }
            });

            // Xử lý sự kiện click nút xác nhận vị trí
            const verifyLocationBtn = document.getElementById('verify-location-btn');
            if (verifyLocationBtn) {
                verifyLocationBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (window.locationVerificationInstance) {
                        // Disable button trong khi đang xử lý
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                        
                        window.locationVerificationInstance.verifyAndUpdate()
                            .catch(function(error) {
                                console.error('Error in verifyAndUpdate:', error);
                            })
                            .finally(function() {
                                // Re-enable button sau 2 giây
                                setTimeout(function() {
                                    verifyLocationBtn.disabled = false;
                                    verifyLocationBtn.innerHTML = originalText;
                                }, 2000);
                            });
                    } else {
                        alert('Lỗi: Không thể khởi tạo hệ thống xác nhận vị trí. Vui lòng tải lại trang.');
                        console.error('LocationVerification instance not found');
                    }
                });
            }
        });

        // Shipping form validation
        function validateShippingForm(e) {
            e.preventDefault();
            
            const form = document.getElementById('shipping-form');
            const shippingName = document.getElementById('shipping_name');
            const shippingPhone = document.getElementById('shipping_phone');
            const shippingEmail = document.getElementById('shipping_email');
            const shippingAddress = document.getElementById('shipping_address');
            const shippingCity = document.getElementById('shipping_city');
            const shippingPostal = document.getElementById('shipping_postal');
            
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-group input').forEach(el => {
                el.style.borderColor = '#e9ecef';
                el.style.background = '#fafbfc';
            });
            
            // Validate name
            if (!shippingName.value.trim() || shippingName.value.trim().length < 2) {
                showFieldError('shipping_name', 'Họ và tên phải có ít nhất 2 ký tự!');
                isValid = false;
            }
            
            // Validate phone
            const phonePattern = /^[0-9]{10,11}$/;
            if (!shippingPhone.value.trim() || !phonePattern.test(shippingPhone.value.trim())) {
                showFieldError('shipping_phone', 'Số điện thoại không hợp lệ! (10-11 chữ số)');
                isValid = false;
            }
            
            // Validate email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!shippingEmail.value.trim() || !emailPattern.test(shippingEmail.value.trim())) {
                showFieldError('shipping_email', 'Email không hợp lệ!');
                isValid = false;
            }
            
            // Validate address
            if (!shippingAddress.value.trim() || shippingAddress.value.trim().length < 10) {
                showFieldError('shipping_address', 'Địa chỉ phải có ít nhất 10 ký tự!');
                isValid = false;
            }
            
            // Validate city
            if (!shippingCity.value.trim() || shippingCity.value.trim().length < 2) {
                showFieldError('shipping_city', 'Thành phố/Tỉnh không hợp lệ!');
                isValid = false;
            }
            
            // Validate postal (optional but if filled, must be valid)
            if (shippingPostal.value.trim()) {
                const postalPattern = /^[0-9]{5,6}$/;
                if (!postalPattern.test(shippingPostal.value.trim())) {
                    showFieldError('shipping_postal', 'Mã bưu chính không hợp lệ! (5-6 chữ số)');
                    isValid = false;
                }
            }
            
            if (isValid) {
                form.submit();
            } else {
                // Scroll to first error
                const firstError = document.querySelector('.field-error:not(:empty)');
                if (firstError) {
                    firstError.closest('.form-group').scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            
            return false;
        }
        
        function showFieldError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorEl = document.getElementById(fieldId + '_error');
            
            if (field) {
                field.style.borderColor = '#e74c3c';
                field.style.background = '#fff5f5';
            }
            
            if (errorEl) {
                errorEl.textContent = message;
            }
        }
        
        // Clear error on input
        document.addEventListener('DOMContentLoaded', function() {
            const formInputs = document.querySelectorAll('#shipping-form input');
            formInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.style.borderColor = '#e9ecef';
                    this.style.background = '#fafbfc';
                    const errorEl = document.getElementById(this.id + '_error');
                    if (errorEl) errorEl.textContent = '';
                });
            });
        });

        // Payment method switching
        document.addEventListener('DOMContentLoaded', function() {
            const paymentOptions = document.querySelectorAll('.payment-option');
            const codForm = document.getElementById('cod-form');
            const vnpayForm = document.getElementById('vnpay-form');

            // Initialize selected state
            paymentOptions.forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                if (radio && radio.checked) {
                    option.classList.add('selected');
                }
            });

            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Check the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        
                        // Handle payment method selection
                        if (radio.value === 'vnpay') {
                            // Hide COD form
                            if (codForm) codForm.style.display = 'none';
                            
                            // Auto-submit VNPay form immediately (with short delay for visual feedback)
                            setTimeout(() => {
                                if (vnpayForm) {
                                    vnpayForm.submit();
                                }
                            }, 500);
                        } else {
                            // Show COD form
                            if (codForm) codForm.style.display = 'flex';
                        }
                    }
                });
            });
            
        });
    </script>
    <?php include('includes/footer.php'); ?>

