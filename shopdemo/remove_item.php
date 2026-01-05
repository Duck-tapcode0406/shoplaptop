<?php
session_start();

// Kiểm tra xem có thông tin về sản phẩm cần xóa trong POST không
if (isset($_POST['order_id']) && isset($_POST['product_id'])) {
    // Lọc và đảm bảo rằng các giá trị là số
    $order_id = intval($_POST['order_id']);
    $product_id = intval($_POST['product_id']);

    // Kết nối cơ sở dữ liệu
    $conn = new mysqli('localhost', 'root', '', 'shop');
    if ($conn->connect_error) {
        die('Kết nối thất bại: ' . $conn->connect_error);
    }

    // Xóa sản phẩm khỏi bảng order_detail
    $delete_sql = "DELETE FROM order_details WHERE order_id = $order_id AND product_id = $product_id";
    if ($conn->query($delete_sql) === TRUE) {
        // Sau khi xóa, thông báo xóa thành công
        echo "<p>Sản phẩm đã được xóa khỏi giỏ hàng.</p>";
    } else {
        // Nếu có lỗi xảy ra khi xóa
        echo "<p>Lỗi khi xóa sản phẩm: " . $conn->error . "</p>";
    }

    // Đóng kết nối cơ sở dữ liệu
    $conn->close();
}

// Quay lại trang giỏ hàng (cart.php)
header("Location: cart.php");
exit();
?>
