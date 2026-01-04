<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Database connection already loaded via includes/db.php

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy tất cả đơn hàng gần đây
$sql_orders = "SELECT o.id, u.username, o.datetime 
               FROM `order` o 
               JOIN user u ON o.customer_id = u.id 
               WHERE o.id IN (
                   SELECT DISTINCT order_id FROM order_details WHERE status = 'paid'
               ) 
               ORDER BY o.datetime DESC Limit 5";
$result_orders = $conn->query($sql_orders);

// Lấy tất cả sản phẩm bán chạy
$sql_products = "SELECT p.id, p.name, SUM(od.quantity) AS total_quantity 
                 FROM product p 
                 JOIN order_details od ON p.id = od.product_id 
                 WHERE od.status = 'paid'
                 GROUP BY p.id 
                 ORDER BY total_quantity DESC limit 5";
$result_products = $conn->query($sql_products);

// Thống kê tổng số khách hàng, sản phẩm, đơn hàng
$sql_summary = "SELECT 
                    (SELECT COUNT(*) FROM user) AS customerCount, 
                    (SELECT COUNT(*) FROM product) AS productCount, 
                    (SELECT COUNT(*) FROM `order` WHERE id IN 
                        (SELECT DISTINCT order_id FROM order_details WHERE status = 'paid')
                    ) AS orderCount";
$result_summary = $conn->query($sql_summary);
$summary = $result_summary->fetch_assoc();

// Tính tổng doanh thu
$sql_revenue = "SELECT SUM(od.quantity * od.price) AS total_revenue
                FROM order_details od
                JOIN product p ON od.product_id = p.id
                WHERE od.status = 'paid'";
$result_revenue = $conn->query($sql_revenue);
$revenue = $result_revenue->fetch_assoc()['total_revenue'];

// Lấy tổng số lượng tất cả sản phẩm đã bán
$sql_total_sales = "SELECT SUM(quantity) AS total_sales 
                    FROM order_details 
                    WHERE status = 'paid'";
$result_total_sales = $conn->query($sql_total_sales);
$total_sales = $result_total_sales->fetch_assoc()['total_sales'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Sidebar dọc */
        .sidebar {
            width: 200px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar h2 {
            color: white;
            margin-bottom: 40px;
            text-align: center;
        }

        .sidebar .nav {
            list-style: none;
            padding-left: 0;
        }

        .sidebar .nav-item {
            margin: 15px 0;
        }

        .sidebar .nav-link {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: block;
        }

        .sidebar .nav-link:hover {
            background-color: #575757;
            border-radius: 5px;
            padding: 10px;
        }

        .content {
            margin-left: 200px;
            padding: 20px;
        }

        .chart-container-summary, .chart-container-pie {
            width: 100%;
            text-align: center;
        }

        .chart-container-summary canvas, .chart-container-pie canvas {
            max-width: 1000px;
            max-height: 400px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="sidebar">
        <h2>Shop Admin</h2>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="index.php">Trang Chủ</a></li>
            <li class="nav-item"><a class="nav-link" href="product.php">Quản lý sản phẩm</a></li>
            <li class="nav-item"><a class="nav-link" href="order.php">Quản lý đơn hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="customers.php">Quản lý khách hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="role.php">Cấp Quyền</a></li>
            <li class="nav-item"><a class="nav-link" href="supplier.php">Nhà cung cấp</a></li>
            <li class="nav-item"><a class="nav-link" href="change_price.php">Đổi giá trong ngày</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Đăng xuất</a></li>
        </ul>
    </div>

    <div class="content">
        <h1 class="mt-4">Admin Dashboard</h1>

        <!-- Biểu đồ thống kê chung -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body chart-container-summary">
                        <h5 class="card-title">Thống kê chung</h5>
                        <canvas id="summaryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ tỷ lệ phần trăm sản phẩm đã bán -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body chart-container-pie">
                        <h5 class="card-title">Tỷ lệ phần trăm sản phẩm đã bán</h5>
                        <canvas id="salesPercentageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doanh thu -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Doanh thu tổng</h5>
                        <p class="card-text">Tổng doanh thu: <?php echo number_format($revenue, 0, ',', '.'); ?> VND</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đơn hàng mới -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Đơn hàng mới</h5>
                        <ul class="list-group list-group-flush">
                            <?php
                            if ($result_orders->num_rows > 0) {
                                while($order = $result_orders->fetch_assoc()) {
                                    echo '<li class="list-group-item">#' . $order['id'] . ' - ' . $order['username'] . ' - ' . date('d/m/Y H:i:s', strtotime($order['datetime'])) . '</li>';
                                }
                            } else {
                                echo '<li class="list-group-item">Không có đơn hàng nào.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sản phẩm bán chạy -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Sản phẩm bán chạy</h5>
                        <ul class="list-group list-group-flush">
                            <?php
                            if ($result_products->num_rows > 0) {
                                while($product = $result_products->fetch_assoc()) {
                                    echo '<li class="list-group-item">' . $product['name'] . ' - ' . $product['total_quantity'] . ' sản phẩm</li>';
                                }
                            } else {
                                echo '<li class="list-group-item">Không có sản phẩm bán chạy.</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Biểu đồ thống kê chung
        const summaryCtx = document.getElementById('summaryChart').getContext('2d');
        new Chart(summaryCtx, {
            type: 'bar',
            data: {
                labels: ['Khách hàng', 'Sản phẩm', 'Đơn hàng'],
                datasets: [{
                    label: 'Thống kê',
                    data: [
                        <?php echo $summary['customerCount']; ?>,
                        <?php echo $summary['productCount']; ?>,
                        <?php echo $summary['orderCount']; ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)'
                    ]
                }]
            }
        });

        // Biểu đồ tỷ lệ phần trăm sản phẩm đã bán
        const salesCtx = document.getElementById('salesPercentageChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'pie',
            data: {
                labels: [<?php
                    $product_labels = [];
                    $product_percentages = [];
                    $result_products->data_seek(0); // Reset pointer
                    while ($product = $result_products->fetch_assoc()) {
                        $product_labels[] = "'" . $product['name'] . "'";
                        $product_percentages[] = ($product['total_quantity'] / $total_sales) * 100;
                    }
                    echo implode(',', $product_labels);
                ?>],
                datasets: [{
                    label: 'Phần trăm',
                    data: [<?php echo implode(',', $product_percentages); ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(255, 205, 86, 0.6)',
                        'rgba(201, 203, 207, 0.6)'
                    ]
                }]
            }
        });
    </script>

</body>
</html>
