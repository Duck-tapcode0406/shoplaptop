<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // show form (simple)
    echo '<form method="POST"><input type="email" name="email" required placeholder="Email">' . getCSRFTokenField() . '<button>Gửi link đặt lại</button></form>';
    exit;
}

validateCSRFPost();

$email = trim($_POST['email'] ?? '');
if (empty($email)) {
    die('Vui lòng nhập email.');
}

$conn = getDB();
$stmt = $conn->prepare('SELECT id, username FROM user WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    // Không tiết lộ user tồn tại hay không — thông báo chung
    echo 'Nếu email tồn tại, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.';
    exit;
}
$user = $res->fetch_assoc();
$user_id = $user['id'];

// Tạo token
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', time() + 1800); // 30 phút

$ins = $conn->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
$ins->bind_param('iss', $user_id, $token, $expires);
$ins->execute();

// Gửi email với link reset (THAY_THẾ bằng mailer của hệ thống)
$resetLink = (defined('BASE_URL') ? BASE_URL : '') . '/reset_password.php?token=' . urlencode($token);
$subject = 'Yêu cầu đặt lại mật khẩu';
$message = "Xin chào,\n\nVui lòng truy cập link sau để đặt lại mật khẩu (hết hạn trong 30 phút): $resetLink\n\nNếu bạn không yêu cầu, vui lòng bỏ qua.";
$headers = 'From: ' . (getenv('SHOP_MAIL_FROM') ?: 'no-reply@example.com') . "\r\n";

// Sử dụng mail() là placeholder — thay bằng SMTP mailer
mail($email, $subject, $message, $headers);

echo 'Nếu email tồn tại, bạn sẽ nhận được hướng dẫn đặt lại mật khẩu.';
?>

