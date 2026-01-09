<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/validator.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF
    validateCSRFPost();
    
    if (isset($_POST['phone'])) {
        // Step 1: Verify phone number
        $phone = Validator::sanitize($_POST['phone'] ?? '');
        $phone_validation = Validator::phone($phone, true);
        
        if (!$phone_validation['valid']) {
            $error_message = $phone_validation['message'];
        } else {
            $cleanPhone = $phone_validation['value'];
            $stmt = $conn->prepare("SELECT id, username, phone FROM user WHERE phone = ? OR phone = ?");
            // Try both with and without +84 prefix
            $altPhone = preg_replace('/^0/', '+84', $cleanPhone);
            $stmt->bind_param("ss", $cleanPhone, $altPhone);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_phone'] = $cleanPhone;
                header('Location: forgot_password.php?step=2');
                exit();
            } else {
                $error_message = "Số điện thoại không tồn tại trong hệ thống!";
            }
        }
    } elseif (isset($_POST['new_password'])) {
        // Step 2: Reset password
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $password_validation = Validator::password($new_password, false);
        
        if (!$password_validation['valid']) {
            $error_message = $password_validation['message'];
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Mật khẩu xác nhận không khớp!";
        } elseif (!isset($_SESSION['reset_user_id'])) {
            $error_message = "Phiên làm việc đã hết hạn. Vui lòng thử lại từ đầu!";
        } else {
            $user_id = $_SESSION['reset_user_id'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_phone']);
                $success_message = "Đặt lại mật khẩu thành công!";
                $step = 3;
            } else {
                $error_message = "Có lỗi xảy ra. Vui lòng thử lại!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu - DNQDH Shop</title>
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
            position: relative;
        }

        .forgot-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: var(--space-5xl);
            text-align: center;
            position: relative;
        }

        .forgot-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-3xl);
            color: white;
            font-size: 48px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .forgot-title {
            font-size: var(--fs-h2);
            font-weight: var(--fw-bold);
            color: #E74C3C;
            margin-bottom: var(--space-md);
        }

        .forgot-subtitle {
            color: var(--text-secondary);
            margin-bottom: var(--space-3xl);
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: var(--space-lg);
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: var(--space-sm);
            font-weight: var(--fw-medium);
            color: var(--text-primary);
            font-size: var(--fs-small);
        }

        .form-group input {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            transition: all var(--transition-fast);
        }

        .form-group input:focus {
            outline: none;
            border-color: #E74C3C;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: var(--space-md);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 18px;
        }

        .action-buttons {
            display: flex;
            gap: var(--space-md);
            margin-top: var(--space-3xl);
        }

        .btn-back {
            flex: 1;
            padding: var(--space-md);
            background: white;
            color: var(--text-primary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            font-weight: var(--fw-medium);
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            transition: all var(--transition-fast);
        }

        .btn-back:hover {
            background: var(--bg-primary);
            border-color: var(--text-secondary);
        }

        .btn-continue {
            flex: 1;
            padding: var(--space-md);
            background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            font-weight: var(--fw-bold);
            cursor: pointer;
            transition: all var(--transition-normal);
            min-height: 48px;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .alert {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            text-align: left;
        }

        .alert-error {
            background-color: #FFF5F5;
            color: #C0392B;
            border: 2px solid #E74C3C;
        }

        .alert-success {
            background-color: #F0FFF4;
            color: #27AE60;
            border: 2px solid #2ECC71;
        }

        .success-icon {
            font-size: 64px;
            color: #2ECC71;
            margin-bottom: var(--space-lg);
        }

        @media (max-width: 576px) {
            .forgot-container {
                padding: var(--space-3xl);
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <!-- Back Button -->
        <a href="login.php" class="back-button" style="position: absolute; top: 20px; left: 20px; z-index: 10;">
            <i class="fas fa-arrow-left"></i>
            Quay lại đăng nhập
        </a>
        
        <?php if ($step == 1): ?>
            <!-- Step 1: Enter Phone -->
            <div class="forgot-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1 class="forgot-title">Tạo mật khẩu mới</h1>
            <p class="forgot-subtitle">Nhập số điện thoại của bạn để bắt đầu quá trình đặt lại mật khẩu</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php echo getCSRFTokenField(); ?>
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" placeholder="VD: 0912345678 hoặc +84912345678" required autocomplete="tel" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="action-buttons">
                    <a href="login.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                    </a>
                    <button type="submit" class="btn-continue">
                        Tiếp tục <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 2): ?>
            <!-- Step 2: Enter New Password -->
            <div class="forgot-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="forgot-title">Tạo mật khẩu mới</h1>
            <p class="forgot-subtitle">Nhập mật khẩu mới cho tài khoản của bạn</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php echo getCSRFTokenField(); ?>
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <div class="password-field">
                        <input type="password" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới (tối thiểu 3 ký tự)" required autocomplete="new-password" minlength="3">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')" aria-label="Hiển thị mật khẩu">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required autocomplete="new-password" minlength="3">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')" aria-label="Hiển thị mật khẩu">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="forgot_password.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <button type="submit" class="btn-continue">
                        Đặt lại mật khẩu <i class="fas fa-check"></i>
                    </button>
                </div>
            </form>

        <?php elseif ($step == 3): ?>
            <!-- Step 3: Success -->
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="forgot-title" style="color: #2ECC71;">Đặt lại mật khẩu thành công!</h1>
            <p class="forgot-subtitle">Mật khẩu của bạn đã được đặt lại thành công. Bạn có thể đăng nhập ngay bây giờ.</p>

            <div class="action-buttons" style="justify-content: center;">
                <a href="login.php" class="btn-continue" style="max-width: 300px;">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleBtn = passwordInput.nextElementSibling.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }

        // Real-time validation
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            // Phone validation
            if (phoneInput) {
                phoneInput.addEventListener('blur', function() {
                    const value = this.value.trim().replace(/[\s\-]/g, '');
                    if (value && !/^(0|\+84|84)[0-9]{9,10}$/.test(value)) {
                        showError(this, 'Số điện thoại không hợp lệ (VD: 0912345678)');
                    } else if (value) {
                        showSuccess(this);
                    }
                });
                phoneInput.addEventListener('input', function() {
                    clearError(this);
                });
            }

            // Password validation
            if (newPasswordInput) {
                newPasswordInput.addEventListener('blur', function() {
                    if (this.value && this.value.length < 3) {
                        showError(this, 'Mật khẩu phải có ít nhất 3 ký tự');
                    } else if (this.value) {
                        showSuccess(this);
                    }
                });
                newPasswordInput.addEventListener('input', function() {
                    clearError(this);
                });
            }

            // Confirm password validation
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('blur', function() {
                    const newPassword = document.getElementById('new_password').value;
                    if (this.value && newPassword !== this.value) {
                        showError(this, 'Mật khẩu xác nhận không khớp');
                    } else if (this.value) {
                        showSuccess(this);
                    }
                });
                confirmPasswordInput.addEventListener('input', function() {
                    clearError(this);
                });
            }
        });

        function showError(input, message) {
            input.style.borderColor = '#e74c3c';
            input.style.background = '#fff5f5';
            
            const parent = input.closest('.form-group') || input.parentElement;
            let existingError = parent.querySelector('.field-error');
            if (existingError) existingError.remove();
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.cssText = 'color: #e74c3c; font-size: 12px; margin-top: 5px; display: flex; align-items: center; gap: 5px;';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
            parent.appendChild(errorDiv);
        }

        function showSuccess(input) {
            input.style.borderColor = '#00b894';
            input.style.background = '#f0fff4';
            
            const parent = input.closest('.form-group') || input.parentElement;
            let existingError = parent.querySelector('.field-error');
            if (existingError) existingError.remove();
        }

        function clearError(input) {
            input.style.borderColor = '#e9ecef';
            input.style.background = 'white';
            
            const parent = input.closest('.form-group') || input.parentElement;
            let existingError = parent.querySelector('.field-error');
            if (existingError) existingError.remove();
        }
    </script>
</body>
</html>

