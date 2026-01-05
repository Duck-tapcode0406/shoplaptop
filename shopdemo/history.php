<?php
session_start();

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning text-center'>Vui lòng đăng nhập để xem lịch sử mua hàng.</div>";
    echo '<div class="text-center"><a href="login.php" class="btn btn-primary">Đăng nhập</a></div>';
    exit();
}

// Lấy thông tin người dùng (id)
$user_id = $_SESSION['user_id'];

// Truy vấn lấy danh sách đơn hàng đã thanh toán và các thông tin màu sắc, cấu hình
$query = "
    SELECT o.id AS order_id, o.datetime, od.product_id, od.quantity, od.price, p.name AS product_name, od.status, od.color_name, od.configuration_name
    FROM `order` o
    JOIN order_details od ON o.id = od.order_id
    JOIN product p ON od.product_id = p.id
    WHERE o.customer_id = $user_id AND od.status = 'paid'
    ORDER BY o.datetime DESC
";

$result = $conn->query($query);
?>
<!-- Bao gồm header từ thư mục includes -->
<?php include('includes/header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Mua Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Lịch Sử Mua Hàng</h1>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID Đơn Hàng</th>
                        <th>Ngày Đặt Hàng</th>
                        <th>Sản Phẩm</th>
                        <th>Số Lượng</th>
                        <th>Giá</th>
                        <th>Tổng Tiền</th>
                        <th>Màu Sắc</th>
                        <th>Cấu Hình</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $product_name = htmlspecialchars($row['product_name']);
                        $quantity = $row['quantity'];
                        $price = $row['price'];
                        $total_price = $quantity * $price;
                        $order_id = $row['order_id'];
                        $order_datetime = $row['datetime'];
                        $status = htmlspecialchars($row['status']);
                        $color_name = $row['color_name'] ?: 'Không có';  // Nếu màu sắc trống, gán 'Không có'
                        $configuration_name = $row['configuration_name'] ?: 'Không có';  // Nếu cấu hình trống, gán 'Không có'
                        ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($order_id); ?></td>
                            <td><?php echo htmlspecialchars($order_datetime); ?></td>
                            <td><?php echo $product_name; ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($quantity); ?></td>
                            <td class="text-end"><?php echo number_format($price, 0, ',', '.'); ?> VND</td>
                            <td class="text-end"><?php echo number_format($total_price, 0, ',', '.'); ?> VND</td>
                            <td class="text-center"><?php echo $color_name; ?></td>
                            <td class="text-center"><?php echo $configuration_name; ?></td>
                            <td class="text-center">
                                <span class="badge bg-success">Đã thanh toán</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">Chưa có đơn hàng nào được thanh toán.</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-primary">Tiếp Tục Mua Sắm</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Include Footer -->
<?php include('footer.php'); ?>
</body>
</html>

<?php $conn->close(); ?>
