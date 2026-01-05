<?php
session_start();
require_once 'includes/db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu có ID sản phẩm được gửi đến
if (isset($_POST['product_id']) && isset($_POST['configuration'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $configuration = explode(' - ', $_POST['configuration']); // Tách cấu hình thành color_name và configuration_name
    $color_name = $configuration[0];
    $configuration_name = $configuration[1];

    // Kiểm tra nếu giỏ hàng đã có sản phẩm này
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = []; // Khởi tạo giỏ hàng nếu chưa có
    }

    // Kiểm tra nếu sản phẩm đã có trong giỏ
    $cart_key = $product_id . '-' . $color_name . '-' . $configuration_name; // Tạo key giỏ hàng theo sản phẩm, màu sắc và cấu hình
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key] += $quantity; // Cộng thêm số lượng
    } else {
        $_SESSION['cart'][$cart_key] = $quantity; // Thêm sản phẩm mới vào giỏ
    }

    // Kiểm tra nếu khách hàng đã có đơn hàng đang xử lý
    if (!isset($_SESSION['order_id'])) {
        // Tạo mới một đơn hàng trong bảng `order`
        $customer_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; // Lấy ID khách hàng từ session
        $datetime = date('Y-m-d H:i:s');
        
        $orderQuery = "INSERT INTO `order` (customer_id, datetime) VALUES ($customer_id, '$datetime')";
        
        if ($conn->query($orderQuery)) {
            $_SESSION['order_id'] = $conn->insert_id; // Lưu lại ID đơn hàng vừa tạo
        } else {
            die("Lỗi khi tạo đơn hàng: " . $conn->error);
        }
    }

    // Lưu thông tin chi tiết sản phẩm vào bảng `order_details`
    $order_id = $_SESSION['order_id'];

    // Truy vấn để lấy giá của sản phẩm mới nhất, kiểm tra nếu có giá tạm thời (giảm giá)
    $priceQuery = "SELECT price, temporary_price, discount_start, discount_end 
                   FROM price 
                   WHERE product_id = $product_id 
                   ORDER BY datetime DESC 
                   LIMIT 1";
    $priceResult = $conn->query($priceQuery);

    if ($priceResult->num_rows > 0) {
        $price_data = $priceResult->fetch_assoc();
        $price = $price_data['price'];

        // Kiểm tra nếu có giá giảm (temporary_price)
        $current_time = date('Y-m-d H:i:s');
        if ($current_time >= $price_data['discount_start'] && $current_time <= $price_data['discount_end']) {
            $price = $price_data['temporary_price']; // Lấy giá tạm thời nếu đang trong thời gian giảm giá
        }

        // Kiểm tra xem sản phẩm đã có trong chi tiết đơn hàng chưa
        $detailCheckQuery = "SELECT * FROM order_details WHERE order_id = $order_id AND product_id = $product_id AND color_name = '$color_name' AND configuration_name = '$configuration_name'";
        $detailCheckResult = $conn->query($detailCheckQuery);

        if ($detailCheckResult->num_rows > 0) {
            // Nếu sản phẩm đã tồn tại, cập nhật số lượng
            $updateQuery = "UPDATE order_details 
                            SET quantity = quantity + $quantity, 
                                price = $price 
                            WHERE order_id = $order_id AND product_id = $product_id AND color_name = '$color_name' AND configuration_name = '$configuration_name'";
            $conn->query($updateQuery);
        } else {
            // Nếu sản phẩm chưa tồn tại, thêm mới vào bảng `order_details`
            $insertQuery = "INSERT INTO order_details (order_id, product_id, quantity, price, color_name, configuration_name) 
                            VALUES ($order_id, $product_id, $quantity, $price, '$color_name', '$configuration_name')";
            $conn->query($insertQuery);
        }
    } else {
        die("Không tìm thấy giá sản phẩm.");
    }
}

// Chuyển hướng về trang sản phẩm (product_detail.php)
header('Location: product_detail.php?id=' . $product_id);
exit();
?>
