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
    <title>Thanh Toán - ModernShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: var(--space-md) var(--space-lg);
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-5xl);
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--border-color);
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--bg-primary);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-md);
            transition: all 0.3s;
        }

        .step.active .step-number {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .step.completed .step-number {
            background-color: var(--success);
            border-color: var(--success);
            color: white;
        }

        .step-name {
            font-size: var(--fs-small);
            font-weight: var(--fw-bold);
            color: var(--text-secondary);
        }

        .step.active .step-name,
        .step.completed .step-name {
            color: var(--primary);
        }

        .checkout-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-2xl);
            box-shadow: var(--shadow-sm);
        }

        .checkout-section {
            margin-bottom: var(--space-3xl);
        }

        .checkout-section h2 {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
            font-size: var(--fs-h3);
        }

        .checkout-section h2 i {
            color: var(--primary);
            font-size: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-lg);
            margin-bottom: var(--space-lg);
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-xs);
            color: var(--text-primary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: var(--space-sm) var(--space-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-lg);
        }

        .payment-option {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option:hover {
            border-color: var(--primary);
            background-color: var(--bg-primary);
        }

        .payment-option input[type="radio"] {
            margin-right: var(--space-md);
        }

        .checkout-actions {
            display: flex;
            gap: var(--space-lg);
            justify-content: space-between;
            margin-top: var(--space-3xl);
            padding-top: var(--space-lg);
            border-top: 1px solid var(--border-color);
        }

        .order-summary {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-top: var(--space-2xl);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: var(--space-md) 0;
            border-bottom: 1px solid var(--border-color);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            padding: var(--space-lg) 0;
            font-size: var(--fs-h3);
            font-weight: var(--fw-bold);
            color: var(--primary);
        }

        .success-message {
            background-color: #F0FFF4;
            border-left: 4px solid var(--success);
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-2xl);
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .success-message i {
            color: var(--success);
            font-size: 24px;
        }

        .error-message {
            background-color: #FFF5F5;
            border-left: 4px solid var(--danger);
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-2xl);
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .error-message i {
            color: var(--danger);
            font-size: 24px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }

            .checkout-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step <?php echo $current_step >= 1 ? 'completed' : ''; ?> <?php echo $current_step === 1 ? 'active' : ''; ?>">
                <div class="step-number">1</div>
                <div class="step-name">Giỏ Hàng</div>
            </div>
            <div class="step <?php echo $current_step >= 2 ? 'completed' : ''; ?> <?php echo $current_step === 2 ? 'active' : ''; ?>">
                <div class="step-number">2</div>
                <div class="step-name">Vận Chuyển</div>
            </div>
            <div class="step <?php echo $current_step >= 3 ? 'completed' : ''; ?> <?php echo $current_step === 3 ? 'active' : ''; ?>">
                <div class="step-number">3</div>
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
                    <form method="POST" id="shipping-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Họ và Tên</label>
                                <input type="text" name="shipping_name" id="shipping_name" required placeholder="Nhập họ và tên">
                            </div>
                            <div class="form-group">
                                <label>Số Điện Thoại</label>
                                <input type="tel" name="shipping_phone" id="shipping_phone" required placeholder="Nhập số điện thoại">
                            </div>
                        </div>
                        <div class="form-row full">
                            <div class="form-group">
                                <label>Địa Chỉ</label>
                                <input type="text" name="shipping_address" id="shipping_address" required placeholder="Nhập địa chỉ giao hàng">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Thành Phố/Tỉnh</label>
                                <input type="text" name="shipping_city" id="shipping_city" required placeholder="Nhập thành phố">
                            </div>
                            <div class="form-group">
                                <label>Mã Bưu Chính</label>
                                <input type="text" name="shipping_postal" id="shipping_postal" placeholder="Nhập mã bưu chính">
                            </div>
                        </div>
                        <div class="checkout-actions">
                            <a href="cart.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
                            <a href="checkout.php?step=3" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Tiếp Tục</a>
                        </div>
                    </form>
                </div>

            <!-- Step 3: Payment -->
            <?php elseif ($current_step === 3): ?>
                <div class="checkout-section">
                    <h2><i class="fas fa-credit-card"></i> Phương Thức Thanh Toán</h2>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <i class="fas fa-money-bill"></i>
                            Thanh toán khi nhận hàng (COD)
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="card">
                            <i class="fas fa-credit-card"></i>
                            Thẻ tín dụng/Ghi nợ
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
                            <span><?php echo number_format($total, 0, ',', '.'); ?> ₫</span>
                        </div>
                    </div>

                    <form method="POST" class="checkout-actions" style="margin-top: var(--space-2xl);">
                        <?php echo getCSRFTokenField(); ?>
                        <a href="checkout.php?step=2" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
                        <button type="submit" name="checkout_all" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i> Xác Nhận Thanh Toán
                        </button>
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
    </div>

    <script src="js/location-verification.js"></script>
    <script>
        // Khởi tạo Location Verification
        window.locationVerificationInstance = new LocationVerification({
            apiEndpoint: 'api/update_location.php',
            onSuccess: function(data) {
                console.log('Đã cập nhật địa chỉ:', data);
            },
            onError: function(message) {
                console.error('Lỗi:', message);
            },
            onLocationFound: function(place, location) {
                // Tự động điền form với thông tin địa chỉ
                if (place.address) {
                    document.getElementById('shipping_address').value = place.address;
                }
                if (place.title) {
                    // Có thể sử dụng title làm địa chỉ nếu không có address
                    if (!place.address) {
                        document.getElementById('shipping_address').value = place.title;
                    }
                }
                
                // Trích xuất thành phố từ địa chỉ
                if (place.address) {
                    const addressParts = place.address.split(',');
                    if (addressParts.length > 0) {
                        document.getElementById('shipping_city').value = addressParts[addressParts.length - 1].trim();
                    }
                }
                
                // Điền số điện thoại nếu có
                if (place.phone) {
                    document.getElementById('shipping_phone').value = place.phone;
                }
            }
        });

        // Xử lý sự kiện click nút xác nhận vị trí
        document.getElementById('verify-location-btn')?.addEventListener('click', function() {
            window.locationVerificationInstance.verifyAndUpdate();
        });
    </script>
    <?php include('includes/footer.php'); ?>
