<?php
header('Content-Type: application/json');

// Kết nối CSDL
$conn = new mysqli('localhost', 'username', 'password', 'shop');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Lấy 3 đơn hàng mới nhất
$orderResult = $conn->query("SELECT o.id, u.username, o.datetime 
                             FROM `order` o 
                             JOIN user u ON o.customer_id = u.id 
                             ORDER BY o.datetime DESC 
                             LIMIT 3");
$orders = $orderResult->fetch_all(MYSQLI_ASSOC);

// Lấy 3 sản phẩm bán chạy nhất
$productResult = $conn->query("SELECT p.id as product_id, p.name, SUM(od.quantity) as total_quantity 
                               FROM product p 
                               JOIN order_details od ON p.id = od.product_id 
                               GROUP BY p.id 
                               ORDER BY total_quantity DESC 
                               LIMIT 3");
$products = $productResult->fetch_all(MYSQLI_ASSOC);

// Lấy thống kê chung
$summaryResult = $conn->query("SELECT 
                                (SELECT COUNT(*) FROM user) AS customerCount, 
                                (SELECT COUNT(*) FROM product) AS productCount, 
                                (SELECT COUNT(*) FROM `order`) AS orderCount");
$summary = $summaryResult->fetch_assoc();

echo json_encode([
    'orders' => $orders,
    'products' => $products,
    'summary' => $summary
]);
