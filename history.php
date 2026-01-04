<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch order history using prepared statement
$query = "
    SELECT o.id AS order_id, o.datetime, od.product_id, od.quantity, od.price, p.name AS product_name, od.status, od.color_name, od.configuration_name
    FROM `order` o
    JOIN order_details od ON o.id = od.order_id
    JOIN product p ON od.product_id = p.id
    WHERE o.customer_id = ? AND od.status = 'paid'
    ORDER BY o.datetime DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php include('includes/header.php'); ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Mua Hàng - DuckShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .history-header {
            margin-bottom: var(--space-3xl);
            padding-bottom: var(--space-lg);
            border-bottom: 2px solid var(--border-color);
        }

        .history-header h1 {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin: 0;
        }

        .history-header i {
            color: var(--primary);
            font-size: 32px;
        }

        .orders-container {
            display: grid;
            gap: var(--space-2xl);
            margin-bottom: var(--space-5xl);
        }

        .order-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }

        .order-card:hover {
            box-shadow: var(--shadow-md);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space-lg);
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: var(--space-md);
        }

        .order-id {
            font-weight: var(--fw-bold);
            font-size: var(--fs-h4);
            color: var(--primary);
        }

        .order-date {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .order-status {
            background-color: #F0FFF4;
            color: var(--success);
            padding: var(--space-xs) var(--space-md);
            border-radius: var(--radius-full);
            font-weight: var(--fw-bold);
            font-size: var(--fs-small);
        }

        .order-items {
            margin-bottom: var(--space-lg);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-md) 0;
            border-bottom: 1px solid var(--bg-primary);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-xs);
        }

        .item-meta {
            font-size: var(--fs-small);
            color: var(--text-secondary);
        }

        .item-price {
            text-align: right;
        }

        .item-total {
            font-weight: var(--fw-bold);
            color: var(--primary);
            font-size: var(--fs-h5);
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: var(--space-lg);
            border-top: 1px solid var(--border-color);
        }

        .order-total {
            text-align: right;
        }

        .order-total label {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .order-total .amount {
            font-size: var(--fs-h3);
            font-weight: var(--fw-bold);
            color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: var(--space-5xl) var(--space-md);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: var(--space-lg);
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--space-md);
            }

            .item-price {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="history-container">
        <!-- Page Header -->
        <div class="history-header">
            <h1>
                <i class="fas fa-receipt"></i>
                Lịch Sử Mua Hàng
            </h1>
        </div>

        <!-- Orders -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="orders-container">
                <?php
                $orders = [];
                while ($row = $result->fetch_assoc()) {
                    $order_id = $row['order_id'];
                    if (!isset($orders[$order_id])) {
                        $orders[$order_id] = [
                            'id' => $order_id,
                            'datetime' => $row['datetime'],
                            'items' => [],
                            'total' => 0
                        ];
                    }
                    $item_total = $row['quantity'] * $row['price'];
                    $orders[$order_id]['items'][] = $row;
                    $orders[$order_id]['total'] += $item_total;
                }

                foreach ($orders as $order):
                ?>
                    <div class="order-card">
                        <!-- Order Header -->
                        <div class="order-header">
                            <div>
                                <div class="order-id">Đơn Hàng #<?php echo $order['id']; ?></div>
                                <div class="order-date">
                                    <i class="fas fa-calendar" style="margin-right: 5px;"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($order['datetime'])); ?>
                                </div>
                            </div>
                            <div class="order-status">
                                <i class="fas fa-check-circle" style="margin-right: 5px;"></i>
                                Đã Thanh Toán
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="order-items">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item">
                                    <div class="item-details">
                                        <div class="item-name">
                                            <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" style="color: var(--text-primary); text-decoration: none;">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </a>
                                        </div>
                                        <div class="item-meta">
                                            <span>SL: <strong><?php echo $item['quantity']; ?></strong></span>
                                            <?php if ($item['color_name']): ?>
                                                <span style="margin-left: var(--space-md);">Màu: <strong><?php echo htmlspecialchars($item['color_name']); ?></strong></span>
                                            <?php endif; ?>
                                            <?php if ($item['configuration_name']): ?>
                                                <span style="margin-left: var(--space-md);">Cấu hình: <strong><?php echo htmlspecialchars($item['configuration_name']); ?></strong></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="item-price">
                                        <div style="font-size: var(--fs-small); color: var(--text-secondary); margin-bottom: 4px;">
                                            <?php echo number_format($item['price'], 0, ',', '.'); ?> ₫ x <?php echo $item['quantity']; ?>
                                        </div>
                                        <div class="item-total">
                                            <?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?> ₫
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Footer -->
                        <div class="order-footer">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-shopping-bag"></i> Mua Lại
                            </a>
                            <div class="order-total">
                                <label>Tổng Cộng</label>
                                <div class="amount">
                                    <?php echo number_format($order['total'], 0, ',', '.'); ?> ₫
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Chưa có đơn hàng nào</h2>
                <p style="color: var(--text-secondary); margin-bottom: var(--space-2xl);">Bạn chưa mua sắm sản phẩm nào từ DuckShop</p>
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Bắt Đầu Mua Sắm
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Include Footer -->
<?php include('footer.php'); ?>
</body>
</html>

<?php $conn->close(); ?>
