<?php
session_start();

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Kiểm tra dữ liệu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $comment_content = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $user_id = $_SESSION['user_id'];

    // Kiểm tra giá trị hợp lệ
    if ($product_id <= 0 || empty($comment_content)) {
        echo "Dữ liệu không hợp lệ.";
        exit();
    }

    // Chuẩn bị câu truy vấn
    $stmt = $conn->prepare("INSERT INTO comment (product_id, user_id, content, created_at) VALUES (?, ?, ?, ?)");
    $created_at = date('Y-m-d H:i:s');
    $stmt->bind_param('iiss', $product_id, $user_id, $comment_content, $created_at);

    // Thực thi câu truy vấn
    if ($stmt->execute()) {
        // Quay lại trang chi tiết sản phẩm
        header("Location: product_detail.php?id=$product_id");
        exit();
    } else {
        echo "Lỗi khi thêm bình luận: " . $stmt->error;
    }

    // Đóng kết nối
    $stmt->close();
} else {
    echo "Phương thức không hợp lệ.";
}

$conn->close();
?>
