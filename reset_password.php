<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($token)) {
        die('Token không hợp lệ.');
    }
    // Show form
    echo '<form method="POST">' . getCSRFTokenField();
    echo '<input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES, "UTF-8") . '">';
    echo '<input type="password" name="new_password" required minlength="6" placeholder="Mật khẩu mới">';
    echo '<input type="password" name="confirm_password" required minlength="6" placeholder="Xác nhận mật khẩu">';
    echo '<button>Đặt lại mật khẩu</button></form>';
    exit;
}

validateCSRFPost();
$token = $_POST['token'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
if ($new !== $confirm || strlen($new) < 6) {
    die('Mật khẩu không hợp lệ hoặc không khớp.');
}

$conn = getDB();
$stmt = $conn->prepare('SELECT id, user_id, expires_at, used FROM password_resets WHERE token = ? LIMIT 1');
$stmt->bind_param('s', $token);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    die('Token không hợp lệ.');
}
$row = $r->fetch_assoc();
if ($row['used']) {
    die('Token đã được sử dụng.');
}
if (strtotime($row['expires_at']) < time()) {
    die('Token đã hết hạn.');
}

$user_id = $row['user_id'];
$hashed = password_hash($new, PASSWORD_DEFAULT);

// Transaction: cập nhật password và đánh dấu token used
$conn->begin_transaction();
$up = $conn->prepare('UPDATE user SET password = ? WHERE id = ?');
$up->bind_param('si', $hashed, $user_id);
$ok1 = $up->execute();

$mark = $conn->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
$mark->bind_param('i', $row['id']);
$ok2 = $mark->execute();

if ($ok1 && $ok2) {
    $conn->commit();
    echo 'Đặt lại mật khẩu thành công.';
} else {
    $conn->rollback();
    echo 'Lỗi khi đặt lại mật khẩu.';
}
?>

