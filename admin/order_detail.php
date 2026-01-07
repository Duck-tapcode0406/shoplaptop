<?php
$page_title = 'Chi tiết đơn hàng';
require_once 'includes/admin_header.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$order_id) {
    header('Location: orders.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (validateCSRFPost()) {
        $new_status = $_POST['new_status'];
        $allowed = ['pending', 'processing', 'shipped', 'paid', 'cancelled'];
        if (in_array($new_status, $allowed)) {
            $stmt = $conn->prepare("UPDATE order_details SET status = ? WHERE order_id = ?");
            $stmt->bind_param('si', $new_status, $order_id);
            $stmt->execute();
            $success_message = 'Cập nhật trạng thái thành công!';
        }
    }
}

// Get order info
$order = $conn->query("
    SELECT o.*, u.username, u.email, u.phone, u.address,
           (SELECT status FROM order_details WHERE order_id = o.id LIMIT 1) as status
    FROM `order` o
    JOIN user u ON o.customer_id = u.id
    WHERE o.id = $order_id
")->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$items = $conn->query("
    SELECT od.*, p.name, p.description, i.path as image
    FROM order_details od
    JOIN product p ON od.product_id = p.id
    LEFT JOIN image i ON p.id = i.product_id AND i.sort_order = 1
    WHERE od.order_id = $order_id
");

// Calculate totals
$subtotal = 0;
$total_items = 0;
$items_array = [];
while ($item = $items->fetch_assoc()) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
    $items_array[] = $item;
}

// Get payment info
$payment = $conn->query("SELECT * FROM payments WHERE order_id = $order_id ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

$status_class = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'info',
    'paid' => 'success',
    'cancelled' => 'danger'
];
$status_text = [
    'pending' => 'Chờ xử lý',
    'processing' => 'Đang xử lý',
    'shipped' => 'Đang giao',
    'paid' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Đơn hàng #<?php echo $order_id; ?></h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <a href="orders.php">Đơn hàng</a>
            <span>/</span>
            <span>#<?php echo $order_id; ?></span>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <button class="btn btn-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> In đơn
        </button>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</div>

<?php if ($success_message): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
    <!-- Order Items -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sản phẩm đặt hàng (<?php echo $total_items; ?> sản phẩm)</h3>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Cấu hình</th>
                        <th>Đơn giá</th>
                        <th>SL</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items_array as $item): ?>
                    <tr>
                        <td>
                            <div class="product-cell">
                                <img src="<?php echo $item['image'] ? $item['image'] : '../images/no-image.png'; ?>" 
                                     class="product-img" alt="">
                                <div>
                                    <strong><?php echo htmlspecialchars(mb_substr($item['name'], 0, 40)); ?></strong>
                                    <?php if (strlen($item['name']) > 40): ?>...<?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($item['color_name'] || $item['configuration_name']): ?>
                                <?php echo htmlspecialchars($item['color_name']); ?> 
                                <?php if ($item['configuration_name']): ?>/ <?php echo htmlspecialchars($item['configuration_name']); ?><?php endif; ?>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td style="font-weight: 600;">
                            <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right; font-weight: 600;">Tạm tính:</td>
                        <td style="font-weight: 600;"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align: right;">Phí vận chuyển:</td>
                        <td>0đ</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td colspan="4" style="text-align: right; font-weight: 700; font-size: 16px;">Tổng cộng:</td>
                        <td style="font-weight: 700; font-size: 16px; color: var(--primary);">
                            <?php echo number_format($subtotal, 0, ',', '.'); ?>đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Payment Info -->
        <?php if ($payment): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-credit-card"></i> Thông tin thanh toán</h3>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <p><strong>Phương thức:</strong> 
                        <?php echo $payment['payment_method'] == 'vnpay' ? 'VNPay' : 'Thanh toán khi nhận hàng'; ?>
                    </p>
                    <p><strong>Mã giao dịch:</strong> <?php echo $payment['transaction_no'] ?? '-'; ?></p>
                    <p><strong>Ngân hàng:</strong> <?php echo $payment['bank_code'] ?? '-'; ?></p>
                </div>
                <div>
                    <p><strong>Số tiền:</strong> <?php echo number_format($payment['amount'], 0, ',', '.'); ?>đ</p>
                    <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></p>
                    <p><strong>Trạng thái:</strong> 
                        <span class="status-badge <?php echo $payment['status'] == 'success' ? 'success' : 'warning'; ?>">
                            <?php echo $payment['status'] == 'success' ? 'Thành công' : 'Chờ xử lý'; ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar -->
    <div>
        <!-- Order Status -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Trạng thái đơn hàng</h3>
            </div>
            
            <div style="text-align: center; padding: 20px 0;">
                <span class="status-badge <?php echo $status_class[$order['status']] ?? 'info'; ?>" 
                      style="font-size: 16px; padding: 10px 25px;">
                    <?php echo $status_text[$order['status']] ?? $order['status']; ?>
                </span>
            </div>
            
            <form method="POST">
                <?php echo getCSRFTokenField(); ?>
                <input type="hidden" name="update_status" value="1">
                
                <div class="form-group">
                    <label class="form-label">Cập nhật trạng thái</label>
                    <select name="new_status" class="form-control">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Đang giao hàng</option>
                        <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>Hoàn thành</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
            </form>
        </div>
        
        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> Thông tin khách hàng</h3>
            </div>
            
            <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'Chưa cập nhật'); ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address'] ?? 'Chưa cập nhật'); ?></p>
            
            <hr style="margin: 15px 0;">
            
            <a href="customer_detail.php?id=<?php echo $order['customer_id']; ?>" class="btn btn-secondary" style="width: 100%;">
                <i class="fas fa-user"></i> Xem khách hàng
            </a>
        </div>
        
        <!-- Order Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h3>
            </div>
            
            <p><strong>Mã đơn:</strong> #<?php echo $order_id; ?></p>
            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i:s', strtotime($order['datetime'])); ?></p>
            <p><strong>Tổng SP:</strong> <?php echo $total_items; ?> sản phẩm</p>
            <p><strong>Tổng tiền:</strong> <span style="color: var(--primary); font-weight: 700;"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span></p>
        </div>
    </div>
</div>

<style>
@media print {
    .admin-sidebar, .admin-header, .btn, form, .page-breadcrumb { display: none !important; }
    .admin-content { margin: 0 !important; padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd; }
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>

