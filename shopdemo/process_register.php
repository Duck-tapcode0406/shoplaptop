<?php
session_start(); // Bắt đầu session

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Lấy dữ liệu từ form
$username = $_POST['username'];
$password = $_POST['password'];

// Truy vấn thông tin người dùng
$sql = "SELECT * FROM `user` WHERE `username` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Kiểm tra mật khẩu
    if (password_verify($password, $user['password'])) {
        // Lưu thông tin người dùng vào session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Chuyển hướng đến trang index.php
        header('Location: index.php');
        exit();
    } else {
        echo "Sai mật khẩu! <a href='login.php'>Thử lại</a>";
    }
} else {
    echo "Tài khoản không tồn tại! <a href='login.php'>Thử lại</a>";
}

// Đóng kết nối
$conn->close();
?>
