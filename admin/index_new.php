<?php
$page_title = 'Dashboard';
require_once 'includes/admin_header.php';

// Get statistics
$stats = [];

// Total customers
$result = $conn->query("SELECT COUNT(*) as cnt FROM user");
$stats['customers'] = $result->fetch_assoc()['cnt'];

// Total products
$result = $conn->query("SELECT COUNT(*) as cnt FROM product");
$stats['products'] = $result->fetch_assoc()['cnt'];

// Total orders (paid)
$result = $conn->query("SELECT COUNT(DISTINCT order_id) as cnt FROM order_details WHERE status = 'paid'");
$stats['orders'] = $result->fetch_assoc()['cnt'];

// Total revenue
$result = $conn->query("SELECT SUM(quantity * price) as total FROM order_details WHERE status = 'paid'");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent orders
$recent_orders = $conn->query("
    SELECT o.id, o.datetime, u.username, u.email,
           SUM(od.quantity * od.price) as total,
           od.status
    FROM `order` o
    JOIN user u ON o.customer_id = u.id
    JOIN order_details od ON o.id = od.order_id
    GROUP BY o.id
    ORDER BY o.datetime DESC
    LIMIT 5
");

// Top selling products
$top_products = $conn->query("
    SELECT p.id, p.name, i.path as image, SUM(od.quantity) as sold, pr.price
    FROM product p
    LEFT JOIN image i ON p.id = i.product_id
    LEFT JOIN price pr ON p.id = pr.product_id
    JOIN order_details od ON p.id = od.product_id AND od.status = 'paid'
    GROUP BY p.id
    ORDER BY sold DESC
    LIMIT 5
");

// Monthly revenue for chart
$monthly_revenue = $conn->query("
    SELECT 
        MONTH(o.datetime) as month,
        SUM(od.quantity * od.price) as revenue
    FROM `order` o
    JOIN order_details od ON o.id = od.order_id
    WHERE od.status = 'paid' AND YEAR(o.datetime) = YEAR(CURDATE())
    GROUP BY MONTH(o.datetime)
    ORDER BY month
");

$months = [];
$revenues = [];
while ($row = $monthly_revenue->fetch_assoc()) {
    $months[] = 'T' . $row['month'];
    $revenues[] = $row['revenue'];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Dashboard</span>
        </div>
    </div>
    <div>
        <span style="color: #999;">Hôm nay: <?php echo date('d/m/Y'); ?></span>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($stats['customers']); ?></h3>
            <p>Khách hàng</p>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> 12% so với tháng trước
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($stats['products']); ?></h3>
            <p>Sản phẩm</p>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> 5 sản phẩm mới
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($stats['orders']); ?></h3>
            <p>Đơn hàng</p>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> 8% so với tháng trước
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($stats['revenue'], 0, ',', '.'); ?>đ</h3>
            <p>Doanh thu</p>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> 15% so với tháng trước
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 25px;">
    <!-- Revenue Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Doanh thu theo tháng</h3>
            <select class="form-control" style="width: auto;">
                <option>Năm <?php echo date('Y'); ?></option>
            </select>
        </div>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
    
    <!-- Order Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Trạng thái đơn hàng</h3>
        </div>
        <canvas id="orderStatusChart" height="200"></canvas>
        <?php
        $status_counts = $conn->query("
            SELECT status, COUNT(DISTINCT order_id) as cnt 
            FROM order_details 
            GROUP BY status
        ");
        $statuses = [];
        $status_values = [];
        while ($row = $status_counts->fetch_assoc()) {
            $statuses[] = $row['status'] == 'paid' ? 'Đã thanh toán' : ($row['status'] == 'pending' ? 'Chờ xử lý' : $row['status']);
            $status_values[] = $row['cnt'];
        }
        ?>
    </div>
</div>

<!-- Tables Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Đơn hàng gần đây</h3>
            <a href="orders.php" class="btn btn-sm btn-secondary">Xem tất cả</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td><?php echo number_format($order['total'], 0, ',', '.'); ?>đ</td>
                    <td>
                        <span class="status-badge <?php echo $order['status'] == 'paid' ? 'success' : 'warning'; ?>">
                            <?php echo $order['status'] == 'paid' ? 'Đã thanh toán' : 'Chờ xử lý'; ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Top Products -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sản phẩm bán chạy</h3>
            <a href="products.php" class="btn btn-sm btn-secondary">Xem tất cả</a>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đã bán</th>
                    <th>Giá</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $top_products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="product-cell">
                            <img src="<?php echo $product['image'] ? $product['image'] : '../images/no-image.png'; ?>" 
                                 class="product-img" alt="">
                            <span><?php echo htmlspecialchars(mb_substr($product['name'], 0, 30)); ?>...</span>
                        </div>
                    </td>
                    <td><?php echo number_format($product['sold']); ?></td>
                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Doanh thu',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#6C5CE7',
            backgroundColor: 'rgba(108, 92, 231, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + 'đ';
                    }
                }
            }
        }
    }
});

// Order Status Chart
const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($statuses); ?>,
        datasets: [{
            data: <?php echo json_encode($status_values); ?>,
            backgroundColor: ['#00b894', '#fdcb6e', '#e74c3c', '#0984e3']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>

