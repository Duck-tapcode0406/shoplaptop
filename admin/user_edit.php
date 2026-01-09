<?php
$page_title = 'Sửa người dùng';
require_once 'includes/admin_header.php';

$success_message = '';
$error_message = '';

// Get user ID
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$user_id) {
    header('Location: users.php');
    exit;
}

// Get user data
$user = $conn->query("SELECT * FROM user WHERE id = $user_id")->fetch_assoc();
if (!$user) {
    header('Location: users.php');
    exit;
}

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
        if (empty($username) || empty($email)) {
            $error_message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Email không hợp lệ!';
        } elseif (!empty($password) && strlen($password) < 3) {
            $error_message = 'Mật khẩu phải có ít nhất 3 ký tự!';
        } else {
            // Check duplicate (exclude current user)
            $check = $conn->prepare("SELECT id FROM user WHERE (username = ? OR email = ?) AND id != ?");
            $check->bind_param('ssi', $username, $email, $user_id);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $error_message = 'Username hoặc email đã tồn tại!';
            } else {
                // Update user
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE user SET username = ?, email = ?, password = ?, phone = ?, is_admin = ? WHERE id = ?");
                    $stmt->bind_param('ssssii', $username, $email, $hashed_password, $phone, $is_admin, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE user SET username = ?, email = ?, phone = ?, is_admin = ? WHERE id = ?");
                    $stmt->bind_param('sssii', $username, $email, $phone, $is_admin, $user_id);
                }
                
                if ($stmt->execute()) {
                    $success_message = 'Cập nhật thành công!';
                    // Refresh user data
                    $user = $conn->query("SELECT * FROM user WHERE id = $user_id")->fetch_assoc();
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
        <h1 class="page-title">Sửa người dùng</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <a href="users.php">Người dùng</a>
            <span>/</span>
            <span>Sửa</span>
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

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 25px;">
    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông tin người dùng</h3>
        </div>
        
        <form method="POST">
            <?php echo getCSRFTokenField(); ?>
            
            <div class="form-group">
                <label class="form-label">Username <span style="color: red;">*</span></label>
                <input type="text" name="username" class="form-control" required
                       value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Email <span style="color: red;">*</span></label>
                <input type="email" name="email" class="form-control" required
                       value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Mật khẩu mới</label>
                <input type="password" name="password" class="form-control" minlength="3"
                       placeholder="Để trống nếu không đổi mật khẩu">
                <small style="color: #999;">Tối thiểu 3 ký tự</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="phone" class="form-control"
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Vai trò</label>
                <select name="is_admin" class="form-control" <?php echo $user_id == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                    <option value="0" <?php echo ($user['is_admin'] ?? 0) == 0 ? 'selected' : ''; ?>>Khách hàng</option>
                    <option value="1" <?php echo ($user['is_admin'] ?? 0) == 1 ? 'selected' : ''; ?>>Admin</option>
                </select>
                <?php if ($user_id == $_SESSION['user_id']): ?>
                <input type="hidden" name="is_admin" value="<?php echo $user['is_admin'] ?? 0; ?>">
                <small style="color: #999;">Không thể thay đổi quyền của chính mình</small>
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <a href="users.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
    
    <!-- User Info Sidebar -->
    <div>
        <div class="card" style="text-align: center;">
            <?php 
            $avatar = $user['avatar'] ?? '';
            if ($avatar && file_exists('../' . $avatar)) {
                $avatar_url = '../' . $avatar;
            } else {
                $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=6c5ce7&color=fff&size=120';
            }
            ?>
            <img src="<?php echo $avatar_url; ?>" 
                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; border: 4px solid var(--primary);">
            
            <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['username']); ?></h3>
            <p style="color: #999; margin-bottom: 15px;"><?php echo htmlspecialchars($user['email']); ?></p>
            
            <?php if (($user['is_admin'] ?? 0) == 1): ?>
            <span class="status-badge danger">Admin</span>
            <?php else: ?>
            <span class="status-badge info">Khách hàng</span>
            <?php endif; ?>
            
            <?php if ($user_id == $_SESSION['user_id']): ?>
            <p style="margin-top: 15px; color: var(--warning); font-size: 12px;">
                <i class="fas fa-info-circle"></i> Đây là tài khoản của bạn
            </p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Thông tin thêm</h3>
            </div>
            <p><i class="fas fa-envelope" style="width: 20px; color: #999;"></i> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><i class="fas fa-phone" style="width: 20px; color: #999;"></i> <?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></p>
            <p><i class="fas fa-key" style="width: 20px; color: #999;"></i> ID: <?php echo $user['id']; ?></p>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>



