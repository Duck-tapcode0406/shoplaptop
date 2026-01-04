<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    // Validate CSRF
    validateCSRFPost();
    
    $order_id = intval($_POST['order_id'] ?? 0);
    $product_id = intval($_POST['product_id'] ?? 0);
    $color_name = !empty($_POST['color_name']) ? Validator::sanitize($_POST['color_name']) : null;
    $configuration_name = !empty($_POST['configuration_name']) ? Validator::sanitize($_POST['configuration_name']) : null;

    // Validate order belongs to user
    $check_order = $conn->prepare("SELECT id FROM `order` WHERE id = ? AND customer_id = ?");
    $check_order->bind_param('ii', $order_id, $user_id);
    $check_order->execute();
    $order_result = $check_order->get_result();
    
    if ($order_result->num_rows > 0) {
        $check_status_sql = "SELECT status FROM order_details 
                             WHERE order_id = ? 
                             AND product_id = ? 
                             AND (color_name = ? OR color_name IS NULL) 
                             AND (configuration_name = ? OR configuration_name IS NULL)";
        $check_status_result = $conn->prepare($check_status_sql);
        $check_status_result->bind_param('iiss', $order_id, $product_id, $color_name, $configuration_name);
        $check_status_result->execute();
        $result = $check_status_result->get_result();

        if ($result->num_rows > 0) {
            $status = $result->fetch_assoc()['status'];
            if ($status === 'pending') {
                $delete_sql = "DELETE FROM order_details 
                               WHERE order_id = ? 
                               AND product_id = ? 
                               AND (color_name = ? OR color_name IS NULL) 
                               AND (configuration_name = ? OR configuration_name IS NULL)";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param('iiss', $order_id, $product_id, $color_name, $configuration_name);
                $delete_stmt->execute();
                
                // Redirect to prevent resubmission
                header('Location: cart.php');
                exit();
            }
        }
    }
}

// Fetch cart items using prepared statement
$order_check_sql = "SELECT o.id AS order_id, o.datetime AS order_datetime, od.product_id, od.quantity, od.price, od.status, od.color_name, od.configuration_name
                    FROM `order` o
                    JOIN `order_details` od ON o.id = od.order_id
                    WHERE o.customer_id = ? AND od.status = 'pending'";
$stmt = $conn->prepare($order_check_sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$order_check_result = $stmt->get_result();
?>
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - DuckShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-3xl);
            margin-bottom: var(--space-5xl);
        }

        .cart-items {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
        }

        .cart-item {
            display: flex;
            gap: var(--space-lg);
            padding: var(--space-lg);
            border-bottom: 1px solid var(--border-color);
            align-items: flex-start;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: var(--radius-md);
            flex-shrink: 0;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-sm);
        }

        .item-meta {
            font-size: var(--fs-small);
            color: var(--text-secondary);
            margin-bottom: var(--space-md);
        }

        .item-controls {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-md);
        }

        .qty-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .qty-control button {
            background: none;
            border: none;
            width: 32px;
            height: 32px;
            cursor: pointer;
            color: var(--text-primary);
            transition: background-color 0.2s;
        }

        .qty-control button:hover {
            background-color: var(--bg-primary);
        }

        .qty-control input {
            width: 60px;
            border: none;
            text-align: center;
            font-weight: var(--fw-bold);
            font-size: var(--fs-body);
        }

        .qty-control input:focus {
            outline: 2px solid var(--primary);
            outline-offset: -2px;
        }

        .qty-control.updating input {
            opacity: 0.6;
            pointer-events: none;
        }

        .qty-control.updating::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .item-price {
            font-size: var(--fs-h5);
            font-weight: var(--fw-bold);
            color: var(--primary);
        }

        .item-actions {
            display: flex;
            gap: var(--space-md);
            margin-top: var(--space-md);
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--error);
            cursor: pointer;
            font-size: var(--fs-small);
            transition: color 0.2s;
        }

        .remove-btn:hover {
            color: var(--accent-dark);
        }

        .order-summary {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-md);
            padding-bottom: var(--space-md);
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: var(--space-lg);
        }

        .summary-label {
            color: var(--text-secondary);
        }

        .summary-value {
            font-weight: var(--fw-bold);
        }

        .summary-total {
            font-size: var(--fs-h3);
            color: var(--primary);
        }

        .empty-cart {
            text-align: center;
            padding: var(--space-5xl) var(--space-md);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: var(--space-lg);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: var(--space-lg);
            right: var(--space-lg);
            background: var(--success);
            color: white;
            padding: var(--space-md) var(--space-lg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: var(--space-md);
            animation: slideInRight 0.3s ease;
        }

        .toast.error {
            background: var(--error);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @media (max-width: 768px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }

            .item-image {
                width: 80px;
                height: 80px;
            }

            .cart-item {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <!-- Page Header -->
        <h1 style="margin-bottom: var(--space-lg);">
            <i class="fas fa-shopping-cart" style="margin-right: var(--space-md);"></i>Giỏ Hàng Của Bạn
        </h1>

        <?php if ($order_check_result && $order_check_result->num_rows > 0): ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    <?php $total_amount = 0; ?>
                    <?php while ($row = $order_check_result->fetch_assoc()): ?>
                        <?php
                        $product_id = $row['product_id'];
                        $product_query = "SELECT name, id FROM product WHERE id = ?";
                        $product_stmt = $conn->prepare($product_query);
                        $product_stmt->bind_param('i', $product_id);
                        $product_stmt->execute();
                        $product_result = $product_stmt->get_result();
                        $product_data = $product_result->fetch_assoc();

                        $product_name = htmlspecialchars($product_data['name']);
                        $quantity = $row['quantity'];
                        $price = $row['price'];
                        $item_total = $quantity * $price;
                        $total_amount += $item_total;
                        $order_id = $row['order_id'];
                        $color_name = $row['color_name'];
                        $configuration_name = $row['configuration_name'];
                        
                        // Get product image using prepared statement
                        $img_query = "SELECT path FROM image WHERE product_id = ? AND sort_order = 1";
                        $img_stmt = $conn->prepare($img_query);
                        $img_stmt->bind_param('i', $product_id);
                        $img_stmt->execute();
                        $img_result = $img_stmt->get_result();
                        $img_data = $img_result->fetch_assoc();
                        $img_path = $img_data ? $img_data['path'] : 'placeholder.jpg';
                        ?>

                        <div class="cart-item">
                            <img src="admin/<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo $product_name; ?>" class="item-image">
                            
                            <div class="item-info">
                                <div class="item-name">
                                    <a href="product_detail.php?id=<?php echo $product_id; ?>" style="color: var(--text-primary); text-decoration: none;">
                                        <?php echo $product_name; ?>
                                    </a>
                                </div>
                                
                                <div class="item-meta">
                                    <?php if ($color_name): ?>
                                        <span><strong>Màu:</strong> <?php echo htmlspecialchars($color_name); ?></span>
                                    <?php endif; ?>
                                    <?php if ($configuration_name): ?>
                                        <span style="margin-left: var(--space-md);"><strong>Cấu hình:</strong> <?php echo htmlspecialchars($configuration_name); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="item-controls">
                                    <span style="font-weight: var(--fw-bold); color: var(--text-secondary);">Số lượng:</span>
                                    <div class="qty-control" data-order-id="<?php echo $order_id; ?>" data-product-id="<?php echo $product_id; ?>" data-color="<?php echo htmlspecialchars($color_name ?? ''); ?>" data-config="<?php echo htmlspecialchars($configuration_name ?? ''); ?>">
                                        <button type="button" class="qty-decrease" aria-label="Giảm số lượng">−</button>
                                        <input type="number" class="qty-input" value="<?php echo $quantity; ?>" min="1" data-original-value="<?php echo $quantity; ?>">
                                        <button type="button" class="qty-increase" aria-label="Tăng số lượng">+</button>
                                    </div>
                                </div>

                                <div class="item-price">
                                    <?php echo number_format($item_total, 0, ',', '.'); ?> ₫
                                </div>

                                <div class="item-actions">
                                    <form method="POST" style="display: inline;">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="color_name" value="<?php echo htmlspecialchars($color_name ?? ''); ?>">
                                        <input type="hidden" name="configuration_name" value="<?php echo htmlspecialchars($configuration_name ?? ''); ?>">
                                        <button type="submit" name="remove_item" class="remove-btn">
                                            <i class="fas fa-trash-alt"></i> Xóa
                                        </button>
                                    </form>
                                    <button class="remove-btn" style="color: var(--primary);">
                                        <i class="fas fa-heart"></i> Lưu để sau
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <h3 style="margin-bottom: var(--space-lg);">
                        <i class="fas fa-receipt" style="margin-right: var(--space-sm);"></i>Tóm Tắt Đơn Hàng
                    </h3>

                    <div class="summary-row">
                        <span class="summary-label">Tạm tính:</span>
                        <span class="summary-value"><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Vận chuyển:</span>
                        <span class="summary-value">Miễn phí</span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Giảm giá:</span>
                        <span class="summary-value">0 ₫</span>
                    </div>

                    <div class="summary-row" style="margin-bottom: 0; padding-bottom: 0; border-bottom: none;">
                        <span style="font-size: var(--fs-body-lg); font-weight: var(--fw-bold);">Tổng cộng:</span>
                        <span class="summary-total" id="cart-total"><?php echo number_format($total_amount, 0, ',', '.'); ?> ₫</span>
                    </div>

                    <form method="POST" action="checkout.php" style="margin-top: var(--space-lg);">
                        <?php echo getCSRFTokenField(); ?>
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: var(--space-md);">
                            <i class="fas fa-credit-card"></i> Tiến Hành Thanh Toán
                        </button>
                    </form>

                    <a href="index.php" class="btn btn-secondary btn-lg" style="width: 100%;">
                        <i class="fas fa-arrow-left"></i> Tiếp Tục Mua Sắm
                    </a>

                    <!-- Trust Badges -->
                    <div style="margin-top: var(--space-lg); padding-top: var(--space-lg); border-top: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: var(--space-sm); margin-bottom: var(--space-md); font-size: var(--fs-small);">
                            <i class="fas fa-lock" style="color: var(--success);"></i>
                            <span>Thanh toán an toàn 100%</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--space-sm); font-size: var(--fs-small);">
                            <i class="fas fa-undo" style="color: var(--secondary);"></i>
                            <span>Hoàn trả 30 ngày</span>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-cart">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 style="margin-bottom: var(--space-lg);">Giỏ hàng của bạn đang trống</h2>
                <p style="color: var(--text-secondary); margin-bottom: var(--space-2xl);">Hãy thêm một số sản phẩm để bắt đầu mua sắm!</p>
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Quay Lại Mua Sắm
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include('includes/footer.php'); ?>

    <!-- Toast Notification -->
    <div id="toast" class="toast" style="display: none;">
        <i class="fas fa-check-circle"></i>
        <span id="toast-message"></span>
    </div>

    <script>
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            toast.className = 'toast ' + type;
            toast.style.display = 'flex';
            
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        // Update cart quantity
        function updateQuantity(qtyControl, newQuantity) {
            const orderId = qtyControl.dataset.orderId;
            const productId = qtyControl.dataset.productId;
            const colorName = qtyControl.dataset.color || '';
            const configName = qtyControl.dataset.config || '';
            
            qtyControl.classList.add('updating');
            
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('product_id', productId);
            formData.append('quantity', newQuantity);
            if (colorName) formData.append('color_name', colorName);
            if (configName) formData.append('configuration_name', configName);
            
            fetch('update_cart_quantity.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                qtyControl.classList.remove('updating');
                
                if (data.success) {
                    // Update item total
                    const itemPrice = qtyControl.closest('.cart-item').querySelector('.item-price');
                    if (itemPrice) {
                        itemPrice.textContent = new Intl.NumberFormat('vi-VN').format(data.item_total) + ' ₫';
                    }
                    
                    // Update cart total
                    const cartTotal = document.getElementById('cart-total');
                    if (cartTotal) {
                        cartTotal.textContent = new Intl.NumberFormat('vi-VN').format(data.cart_total) + ' ₫';
                    }
                    
                    showToast('Đã cập nhật số lượng', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                    // Revert input value
                    const input = qtyControl.querySelector('.qty-input');
                    input.value = input.dataset.originalValue;
                }
            })
            .catch(error => {
                qtyControl.classList.remove('updating');
                showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                const input = qtyControl.querySelector('.qty-input');
                input.value = input.dataset.originalValue;
            });
        }

        // Quantity controls
        document.querySelectorAll('.qty-control').forEach(control => {
            const input = control.querySelector('.qty-input');
            const decreaseBtn = control.querySelector('.qty-decrease');
            const increaseBtn = control.querySelector('.qty-increase');
            
            // Decrease button
            decreaseBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                    input.dataset.originalValue = input.value;
                    updateQuantity(control, input.value);
                }
            });
            
            // Increase button
            increaseBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                input.value = value + 1;
                input.dataset.originalValue = input.value;
                updateQuantity(control, input.value);
            });
            
            // Input change
            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (value < 1) {
                    this.value = 1;
                }
                this.dataset.originalValue = this.value;
                updateQuantity(control, this.value);
            });
            
            // Input blur - validate
            input.addEventListener('blur', function() {
                let value = parseInt(this.value);
                if (value < 1 || isNaN(value)) {
                    this.value = this.dataset.originalValue || 1;
                }
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
