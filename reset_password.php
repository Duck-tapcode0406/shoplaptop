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
    // Show form with proper HTML structure
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Đặt Lại Mật Khẩu - DNQDH Shop</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="css/main.css">
        <style>
            body {
                background: #f5f5f5;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: var(--space-md);
            }
            .reset-container {
                max-width: 500px;
                width: 100%;
                background: white;
                border-radius: var(--radius-lg);
                padding: var(--space-3xl);
                box-shadow: var(--shadow-lg);
            }
            .reset-header {
                text-align: center;
                margin-bottom: var(--space-2xl);
            }
            .reset-header h1 {
                color: var(--primary);
                margin-bottom: var(--space-sm);
            }
        </style>
    </head>
    <body>
        <div class="reset-container">
            <!-- Back Button -->
            <a href="login.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Quay lại đăng nhập
            </a>
            
            <div class="reset-header">
                <h1><i class="fas fa-key"></i> Đặt Lại Mật Khẩu</h1>
            </div>
            <form method="POST">
                <?php echo getCSRFTokenField(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, "UTF-8"); ?>">
                
                <div class="form-group">
                    <label>Mật khẩu mới <span style="color: #e74c3c;">*</span></label>
                    <input type="password" name="new_password" required minlength="3" placeholder="Nhập mật khẩu mới (tối thiểu 3 ký tự)" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Xác nhận mật khẩu <span style="color: #e74c3c;">*</span></label>
                    <input type="password" name="confirm_password" required minlength="3" placeholder="Nhập lại mật khẩu mới" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--space-lg);">
                    <i class="fas fa-save"></i> Đặt lại mật khẩu
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

validateCSRFPost();
$token = $_POST['token'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
if ($new !== $confirm || strlen($new) < 3) {
    die('Mật khẩu không hợp lệ hoặc không khớp. Mật khẩu phải có ít nhất 3 ký tự.');
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

