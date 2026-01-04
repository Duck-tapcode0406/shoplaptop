<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/validator.php';
require_once 'includes/helpers.php';

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// Validate CSRF token
validateCSRFPost();

// Lấy và sanitize dữ liệu từ form
$username = Validator::sanitize($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    header('Location: login.php');
    exit();
}

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
        
        // Regenerate session ID after login
        regenerateSessionAfterLogin();

        // Chuyển hướng đến trang index.php
        redirect(BASE_URL . '/index.php');
    } else {
        $_SESSION['error'] = "Sai mật khẩu!";
        header('Location: login.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Tài khoản không tồn tại!";
    header('Location: login.php');
    exit();
}
?>
