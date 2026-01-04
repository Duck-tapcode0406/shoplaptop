<?php
session_start();

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning text-center'>Vui lòng đăng nhập để xem giỏ hàng của bạn.</div>";
    echo '<div class="text-center"><a href="login.php" class="btn btn-primary">Đăng nhập</a></div>';
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];

// Xử lý yêu cầu xóa món hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);
    $color_name = $_POST['color_name'] ?: null;  // Nếu color_name trống, gán NULL
    $configuration_name = $_POST['configuration_name'] ?: null;  // Nếu configuration_name trống, gán NULL

    // Kiểm tra trạng thái của món hàng và xóa nếu trạng thái là 'pending'
    $check_status_sql = "SELECT status FROM order_details 
                         WHERE order_id = $order_id 
                         AND product_id = $product_id 
                         AND (color_name = ? OR color_name IS NULL) 
                         AND (configuration_name = ? OR configuration_name IS NULL)";
    $check_status_result = $conn->prepare($check_status_sql);
    $check_status_result->bind_param('ss', $color_name, $configuration_name);
    $check_status_result->execute();
    $result = $check_status_result->get_result();

    if ($result->num_rows > 0) {
        $status = $result->fetch_assoc()['status'];
        if ($status === 'pending') {
            // Xóa món hàng khỏi giỏ nếu trạng thái là pending
            $delete_sql = "DELETE FROM order_details 
                           WHERE order_id = ? 
                           AND product_id = ? 
                           AND (color_name = ? OR color_name IS NULL) 
                           AND (configuration_name = ? OR configuration_name IS NULL)";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param('iiss', $order_id, $product_id, $color_name, $configuration_name);
            if ($delete_stmt->execute()) {
                echo "<div class='alert alert-success text-center'>Món hàng đã được xóa khỏi giỏ.</div>";
            } else {
                echo "<div class='alert alert-danger text-center'>Lỗi khi xóa món hàng: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-warning text-center'>Món hàng đã thanh toán không thể xóa.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Món hàng không tồn tại.</div>";
    }
}

// Lấy danh sách đơn hàng có trạng thái "pending"
$order_check_sql = "SELECT o.id AS order_id, o.datetime AS order_datetime, od.product_id, od.quantity, od.price, od.status, od.color_name, od.configuration_name
                    FROM `order` o
                    JOIN `order_details` od ON o.id = od.order_id
                    WHERE o.customer_id = $user_id AND od.status = 'pending'";
$order_check_result = $conn->query($order_check_sql);
?>
<!-- Bao gồm header từ thư mục includes -->
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
        }

        footer {
            margin-top: auto;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Giỏ Hàng Của Bạn</h1>

    <?php if ($order_check_result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID Đơn hàng</th>
                        <th>Thời gian</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Tổng tiền</th>
                        <th>Màu sắc</th>
                        <th>Cấu hình</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $order_check_result->fetch_assoc()): ?>
                        <?php
                        $product_id = $row['product_id'];
                        $product_query = "SELECT name FROM product WHERE id = $product_id";
                        $product_result = $conn->query($product_query);
                        $product_data = $product_result->fetch_assoc();

                        $product_name = htmlspecialchars($product_data['name']);
                        $quantity = $row['quantity'];
                        $price = $row['price'];
                        $total_price = $quantity * $price;
                        $order_id = $row['order_id'];
                        $order_datetime = $row['order_datetime'];
                        $status = $row['status'];
                        $color_name = $row['color_name'];
                        $configuration_name = $row['configuration_name'];
                        ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($order_id); ?></td>
                            <td><?php echo htmlspecialchars($order_datetime); ?></td>
                            <td><?php echo $product_name; ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($quantity); ?></td>
                            <td class="text-end"><?php echo number_format($price, 0, ',', '.'); ?> VND</td>
                            <td class="text-end"><?php echo number_format($total_price, 0, ',', '.'); ?> VND</td>
                            <td class="text-center"><?php echo htmlspecialchars($color_name); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($configuration_name); ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $status === 'pending' ? 'warning' : 'success'; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($status === 'pending'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="color_name" value="<?php echo $color_name; ?>">
                                        <input type="hidden" name="configuration_name" value="<?php echo $configuration_name; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Xóa</button>
                                    </form>
                                    <form method="POST" action="checkout.php" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <button type="submit" name="checkout_item" class="btn btn-success btn-sm">Thanh toán</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
            <form method="POST" action="checkout.php">
                <button type="submit" class="btn btn-success">Thanh toán tất cả</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">Giỏ hàng của bạn đang trống.</div>
        <div class="text-center">
            <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
        </div>
    <?php endif; ?>
</div>

<!-- Include Footer -->
<?php include('footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
