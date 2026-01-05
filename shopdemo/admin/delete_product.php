<?php
session_start();

// Kiểm tra trạng thái đăng nhập và quyền của người dùng
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo "Bạn không có quyền thực hiện thao tác này.";
    exit();
}

// Kiểm tra nếu có ID sản phẩm được gửi qua tham số GET
if (isset($_GET['id'])) {
    // Kết nối đến cơ sở dữ liệu
    $conn = new mysqli('localhost', 'root', '', 'shop');

    // Kiểm tra kết nối cơ sở dữ liệu
    if ($conn->connect_error) {
        die('Kết nối thất bại: ' . $conn->connect_error);
    }

    // Lấy ID sản phẩm từ tham số GET và chuyển thành kiểu số nguyên
    $product_id = (int)$_GET['id'];

    // Kiểm tra xem sản phẩm có tồn tại trong cơ sở dữ liệu không
    $sqlCheckProduct = "SELECT * FROM product WHERE id = $product_id";
    $result = $conn->query($sqlCheckProduct);

    if ($result->num_rows > 0) {
        // Bước 1: Xóa các bản ghi trong bảng `receipt_details` tham chiếu đến sản phẩm
        $sqlDeleteReceiptDetails = "DELETE FROM receipt_details WHERE product_id = $product_id";
        if (!$conn->query($sqlDeleteReceiptDetails)) {
            echo "Lỗi khi xóa dữ liệu trong bảng receipt_details: " . $conn->error;
            exit();
        }

        // Bước 2: Xóa các bản ghi trong bảng `colors_configuration` tham chiếu đến sản phẩm
        $sqlDeleteColorsConfig = "DELETE FROM colors_configuration WHERE product_id = $product_id";
        if (!$conn->query($sqlDeleteColorsConfig)) {
            echo "Lỗi khi xóa dữ liệu trong bảng colors_configuration: " . $conn->error;
            exit();
        }

        // Bước 3: Xóa các bản ghi trong bảng `order_details` tham chiếu đến sản phẩm
        $sqlDeleteOrderDetails = "DELETE FROM order_details WHERE product_id = $product_id";
        if (!$conn->query($sqlDeleteOrderDetails)) {
            echo "Lỗi khi xóa dữ liệu trong bảng order_details: " . $conn->error;
            exit();
        }

        // Bước 4: Xóa các bản ghi trong bảng `image` liên quan đến sản phẩm
        $sqlDeleteImages = "DELETE FROM image WHERE product_id = $product_id";
        if (!$conn->query($sqlDeleteImages)) {
            echo "Lỗi khi xóa dữ liệu trong bảng image: " . $conn->error;
            exit();
        }

        // Bước 5: Xóa sản phẩm trong bảng `product`
        $sqlDeleteProduct = "DELETE FROM product WHERE id = $product_id";
        if ($conn->query($sqlDeleteProduct) === TRUE) {
            echo "Sản phẩm đã được xóa thành công.";
            header('Location: product.php'); // Quay lại trang danh sách sản phẩm
            exit();
        } else {
            echo "Lỗi khi xóa sản phẩm: " . $conn->error;
        }
    } else {
        echo "Sản phẩm không tồn tại.";
    }

    // Đóng kết nối
    $conn->close();
} else {
    echo "Không có ID sản phẩm.";
    exit();
}
?>
