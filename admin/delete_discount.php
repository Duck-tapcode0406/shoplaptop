<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "shop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $discount_id = $_GET['id'];

    // Xóa giảm giá từ bảng discount_history
    $sql = "DELETE FROM discount_history WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $discount_id);
    if ($stmt->execute()) {
        echo "Discount record deleted successfully.";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Đóng kết nối
$conn->close();

// Quay lại trang trước đó
header("Location: change_price.php");
exit();
?>
