<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vui lòng đăng nhập để đánh giá sản phẩm.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $user_id = $_SESSION['user_id'];

    // Validation
    if ($product_id <= 0) {
        $response['message'] = 'Sản phẩm không hợp lệ.';
        echo json_encode($response);
        exit();
    }

    if ($rating < 1 || $rating > 5) {
        $response['message'] = 'Xếp hạng phải từ 1 đến 5 sao.';
        echo json_encode($response);
        exit();
    }

    if (empty($content) || strlen($content) < 10) {
        $response['message'] = 'Nội dung đánh giá phải có ít nhất 10 ký tự.';
        echo json_encode($response);
        exit();
    }

    // Check if user already reviewed this product
    $check_stmt = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $check_stmt->bind_param('ii', $product_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response['message'] = 'Bạn đã đánh giá sản phẩm này rồi.';
        echo json_encode($response);
        exit();
    }

    // Optional: Check if user has purchased this product
    // Uncomment if you want to restrict reviews to buyers only
    /*
    $purchase_check = $conn->prepare("
        SELECT od.id 
        FROM order_details od
        JOIN orders o ON od.order_id = o.id
        WHERE od.product_id = ? AND o.user_id = ? AND o.status = 'completed'
        LIMIT 1
    ");
    $purchase_check->bind_param('ii', $product_id, $user_id);
    $purchase_check->execute();
    $purchase_result = $purchase_check->get_result();
    
    if ($purchase_result->num_rows === 0) {
        $response['message'] = 'Bạn chỉ có thể đánh giá sản phẩm đã mua.';
        echo json_encode($response);
        exit();
    }
    */

    // Insert review
    $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiis', $product_id, $user_id, $rating, $content);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Đánh giá của bạn đã được gửi thành công!';
    } else {
        $response['message'] = 'Có lỗi xảy ra khi gửi đánh giá: ' . $conn->error;
    }

    $stmt->close();
} else {
    $response['message'] = 'Phương thức không hợp lệ.';
}

echo json_encode($response);
$conn->close();
?>





