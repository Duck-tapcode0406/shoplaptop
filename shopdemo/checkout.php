<?php
session_start();
require_once 'includes/db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "<p>Vui lòng đăng nhập để thực hiện thanh toán.</p>";
    echo '<a href="login.php">Đăng nhập</a>';
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['checkout_item'])) {
            // Thanh toán sản phẩm cụ thể
            $order_id = intval($_POST['order_id']);
            $product_id = intval($_POST['product_id']);
            // Lấy thông tin màu sắc từ bảng colors_configuration dựa trên product_id
            $get_color_sql = "SELECT id, color_name FROM colors_configuration 
                              WHERE product_id = $product_id LIMIT 1";
            $color_result = $conn->query($get_color_sql);

            if ($color_result->num_rows > 0) {
                $color_row = $color_result->fetch_assoc();
                $color_id = $color_row['id']; // Lấy ID màu
                $color_name = $color_row['color_name']; // Lấy tên màu

                // Kiểm tra trạng thái sản phẩm
                $check_status_sql = "SELECT quantity FROM order_details 
                                     WHERE order_id = $order_id AND product_id = $product_id AND status = 'pending'";
                $check_status_result = $conn->query($check_status_sql);

                if ($check_status_result->num_rows > 0) {
                    $quantity = $check_status_result->fetch_assoc()['quantity'];

                    // Giảm số lượng sản phẩm trong bảng receipt_details
                    $update_quantity_sql = "UPDATE receipt_details 
                                            SET quantity = quantity - $quantity 
                                            WHERE product_id = $product_id";
                    if (!$conn->query($update_quantity_sql)) {
                        throw new Exception("Lỗi giảm số lượng sản phẩm trong receipt_details: " . $conn->error);
                    }

                    // Giảm số lượng trong bảng colors_configuration (so sánh cả product_id và color_id)
                    $update_color_quantity_sql = "UPDATE colors_configuration 
                                                  SET quantity = quantity - $quantity 
                                                  WHERE product_id = $product_id AND id = $color_id";
                    if (!$conn->query($update_color_quantity_sql)) {
                        throw new Exception("Lỗi giảm số lượng trong colors_configuration: " . $conn->error);
                    }

                    // Cập nhật trạng thái đơn hàng
                    $update_status_sql = "UPDATE order_details 
                                          SET status = 'paid' 
                                          WHERE order_id = $order_id AND product_id = $product_id";
                    if (!$conn->query($update_status_sql)) {
                        throw new Exception("Lỗi cập nhật trạng thái đơn hàng: " . $conn->error);
                    }

                    echo "<p>Thanh toán thành công sản phẩm!</p>";
                } else {
                    echo "<p>Sản phẩm không hợp lệ hoặc đã được thanh toán.</p>";
                }
            } else {
                echo "<p>Màu sắc không hợp lệ hoặc không có trong hệ thống.</p>";
            }
        } elseif (isset($_POST['checkout_all'])) {
            // Thanh toán tất cả các sản phẩm
            $order_check_sql = "SELECT o.id AS order_id, od.product_id, od.quantity, c.id AS color_id, c.color_name
                                FROM `order` o
                                JOIN `order_details` od ON o.id = od.order_id
                                JOIN `colors_configuration` c ON c.product_id = od.product_id
                                WHERE o.customer_id = $user_id AND od.status = 'pending'";
            $order_check_result = $conn->query($order_check_sql);

            if ($order_check_result->num_rows > 0) {
                while ($row = $order_check_result->fetch_assoc()) {
                    $order_id = $row['order_id'];
                    $product_id = $row['product_id'];
                    $quantity = $row['quantity'];
                    $color_id = $row['color_id'];  // Lấy color_id từ bảng colors_configuration
                    $color_name = $row['color_name'];  // Lấy color_name từ bảng colors_configuration

                    // Giảm số lượng sản phẩm trong bảng receipt_details
                    $update_quantity_sql = "UPDATE receipt_details 
                                            SET quantity = quantity - $quantity 
                                            WHERE product_id = $product_id";
                    if (!$conn->query($update_quantity_sql)) {
                        throw new Exception("Lỗi giảm số lượng sản phẩm trong receipt_details: " . $conn->error);
                    }

                    // Giảm số lượng trong bảng colors_configuration (so sánh cả product_id và color_id)
                    $update_color_quantity_sql = "UPDATE colors_configuration 
                                                  SET quantity = quantity - $quantity 
                                                  WHERE product_id = $product_id AND id = $color_id";
                    if (!$conn->query($update_color_quantity_sql)) {
                        throw new Exception("Lỗi giảm số lượng trong colors_configuration: " . $conn->error);
                    }

                    // Cập nhật trạng thái đơn hàng
                    $update_status_sql = "UPDATE order_details 
                                          SET status = 'paid' 
                                          WHERE order_id = $order_id AND product_id = $product_id";
                    if (!$conn->query($update_status_sql)) {
                        throw new Exception("Lỗi cập nhật trạng thái đơn hàng: " . $conn->error);
                    }
                }
                echo "<p>Thanh toán thành công tất cả sản phẩm!</p>";
            } else {
                echo "<p>Không có sản phẩm nào để thanh toán.</p>";
            }
        }
    }

    // Commit transaction
    $conn->commit();
    echo '<a href="cart.php">Quay lại giỏ hàng</a>';
} catch (Exception $e) {
    // Rollback transaction nếu có lỗi
    $conn->rollback();
    echo "<p>Đã xảy ra lỗi: " . $e->getMessage() . "</p>";
    echo '<a href="cart.php">Quay lại giỏ hàng</a>';
}

$conn->close();
?>
