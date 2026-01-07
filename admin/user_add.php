<?php
$page_title = 'Thêm người dùng';
require_once 'includes/admin_header.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $is_admin = isset($_POST['is_admin']) ? intval($_POST['is_admin']) : 0;
        
        // Validate
        if (empty($username) || empty($email) || empty($password)) {
            $error_message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        } elseif (strlen($password) < 3) {
            $error_message = 'Mật khẩu phải có ít nhất 3 ký tự!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Email không hợp lệ!';
        } else {
            // Check duplicate
            $check = $conn->prepare("SELECT id FROM user WHERE username = ? OR email = ?");
            $check->bind_param('ss', $username, $email);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $error_message = 'Username hoặc email đã tồn tại!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO user (username, password, email, phone, is_admin) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssi', $username, $hashed_password, $email, $phone, $is_admin);
                
                if ($stmt->execute()) {
                    $success_message = 'Thêm người dùng thành công!';
                    // Clear form
                    $username = $email = $phone = '';
                } else {
                    $error_message = 'Có lỗi xảy ra: ' . $conn->error;
                }
            }
        }
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Thêm người dùng</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <a href="users.php">Người dùng</a>
            <span>/</span>
            <span>Thêm mới</span>
        </div>
    </div>
    <a href="users.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php if ($error_message): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
</div>
<?php endif; ?>

<?php if ($success_message): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
</div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h3 class="card-title">Thông tin người dùng</h3>
    </div>
    
    <form method="POST">
        <?php echo getCSRFTokenField(); ?>
        
        <div class="form-group">
            <label class="form-label">Username <span style="color: red;">*</span></label>
            <input type="text" name="username" class="form-control" required
                   value="<?php echo htmlspecialchars($username ?? ''); ?>"
                   placeholder="Nhập username">
        </div>
        
        <div class="form-group">
            <label class="form-label">Email <span style="color: red;">*</span></label>
            <input type="email" name="email" class="form-control" required
                   value="<?php echo htmlspecialchars($email ?? ''); ?>"
                   placeholder="Nhập email">
        </div>
        
        <div class="form-group">
            <label class="form-label">Mật khẩu <span style="color: red;">*</span></label>
            <input type="password" name="password" class="form-control" required minlength="3"
                   placeholder="Nhập mật khẩu (tối thiểu 3 ký tự)">
        </div>
        
        <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="phone" class="form-control"
                   value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                   placeholder="Nhập số điện thoại">
        </div>
        
        <div class="form-group">
            <label class="form-label">Vai trò</label>
            <select name="is_admin" class="form-control">
                <option value="0">Khách hàng</option>
                <option value="1">Admin</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-save"></i> Lưu người dùng
            </button>
            <a href="users.php" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

<?php require_once 'includes/admin_footer.php'; ?>

