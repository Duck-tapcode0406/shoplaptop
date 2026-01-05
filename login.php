<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/validator.php';
require_once 'includes/helpers.php';
require_once 'includes/config.php';

$error_message = '';
$success_message = '';

// Check if session expired
if (isset($_GET['expired'])) {
    $error_message = "Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    validateCSRFPost();
    
    $input_username = Validator::sanitize($_POST['username'] ?? '');
    $input_password = $_POST['password'] ?? '';

    // Validate input
    if (empty($input_username) || empty($input_password)) {
        $error_message = "Vui lòng nhập tên đăng nhập và mật khẩu!";
    } else {
        // Check login attempts (rate limiting)
        $attempt_check = checkLoginAttempts($input_username);
        if (!$attempt_check['allowed']) {
            $error_message = $attempt_check['message'];
        } else {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
            $stmt->bind_param('s', $input_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($input_password, $row['password'])) {
                    // Reset login attempts on successful login
                    resetLoginAttempts($input_username);
                    
                    // Set session variables
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    
                    // Regenerate session ID after login
                    regenerateSessionAfterLogin();

                    // Check user role using prepared statement
                    $user_id = $row['id'];
                    $role_stmt = $conn->prepare("SELECT r.id AS role_id, r.name AS role_name 
                                                FROM role r 
                                                JOIN user_role ur ON ur.role_id = r.id
                                                WHERE ur.user_id = ? AND r.name = 'admin'");
                    $role_stmt->bind_param('i', $user_id);
                    $role_stmt->execute();
                    $role_result = $role_stmt->get_result();

                    if ($role_result->num_rows > 0) {
                        $role = $role_result->fetch_assoc();
                        $_SESSION['role_id'] = $role['role_id'];
                        $_SESSION['role_name'] = $role['role_name'];
                        redirect(BASE_URL . '/admin/index.php');
                    } else {
                        redirect(BASE_URL . '/index.php');
                    }
                } else {
                    // Increment login attempts on failed login
                    incrementLoginAttempts($input_username);
                    $error_message = "Mật khẩu không chính xác!";
                }
            } else {
                // Increment login attempts on failed login
                incrementLoginAttempts($input_username);
                $error_message = "Tài khoản không tồn tại!";
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
    <title>Đăng Nhập - ModernShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Split Screen Layout */
        .split-screen {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        /* Left Side - Image Background */
        .split-left {
            width: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .split-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200&q=80') center/cover;
            opacity: 0.3;
        }

        .split-left::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }

        .split-left-content {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
            padding: 40px;
            max-width: 500px;
        }

        .split-left-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .split-left-content p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Right Side - Form */
        .split-right {
            width: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            overflow-y: auto;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-card {
            background: white;
            padding: 0;
        }

        .auth-header {
            text-align: center;
            margin-bottom: var(--space-3xl);
        }

        .auth-logo {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: var(--space-md);
        }

        .auth-title {
            font-size: var(--fs-h2);
            font-weight: var(--fw-bold);
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
        }

        .auth-subtitle {
            font-size: var(--fs-small);
            color: var(--text-secondary);
        }

        .auth-form {
            margin-bottom: var(--space-lg);
        }

        .form-group {
            margin-bottom: var(--space-lg);
        }

        .form-group label {
            display: block;
            margin-bottom: var(--space-sm);
            font-weight: var(--fw-medium);
            color: var(--text-primary);
        }

        .form-group input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4169E1;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.1);
        }

        .form-group input::placeholder {
            color: var(--text-light);
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
        }

        .password-field {
            position: relative;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
            font-size: var(--fs-small);
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .forgot-password:hover {
            color: var(--primary-dark);
        }

        .auth-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            min-height: 52px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .auth-button:active {
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .split-screen {
                flex-direction: column;
            }

            .split-left {
                width: 100%;
                height: 40vh;
            }

            .split-right {
                width: 100%;
                height: 60vh;
            }

            .split-left-content h1 {
                font-size: 1.8rem;
            }
        }

        .divider {
            display: flex;
            align-items: center;
            margin: var(--space-2xl) 0;
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .divider::before {
            margin-right: var(--space-md);
        }

        .divider::after {
            margin-left: var(--space-md);
        }

        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }

        .social-btn {
            padding: var(--space-sm);
            border: 1px solid var(--border-color);
            background: white;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            font-size: var(--fs-small);
        }

        .social-btn:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .auth-footer {
            text-align: center;
            font-size: var(--fs-small);
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: var(--fw-medium);
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        .auth-footer a:hover {
            color: var(--primary-dark);
        }

        .alert {
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .alert-error {
            background-color: #FFF5F5;
            color: #742A2A;
            border: 1px solid #FED7D7;
        }

        .alert-success {
            background-color: #F0FFF4;
            color: #22543D;
            border: 1px solid #9AE6B4;
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <!-- Left Side - Image & Quote -->
        <div class="split-left">
            <div class="split-left-content">
                <h1>Chào mừng đến với ModernShop</h1>
                <p>"Công nghệ không chỉ là công cụ, mà là cánh cửa mở ra tương lai. Khám phá thế giới công nghệ với chúng tôi."</p>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="split-right">
            <div class="auth-container">
                <div class="auth-card">
            <!-- Back to Home Button -->
            <div style="margin-bottom: 20px;">
                <a href="index.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary, #4169E1); text-decoration: none; font-size: 14px; transition: color 0.3s;" onmouseover="this.style.color='#1E90FF'" onmouseout="this.style.color='var(--primary, #4169E1)'">
                    <i class="fas fa-arrow-left"></i>
                    <span>Quay lại trang chủ</span>
                </a>
            </div>
            
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h1 class="auth-title">ModernShop</h1>
                <p class="auth-subtitle">Đăng nhập để tiếp tục</p>
            </div>

            <!-- Alerts -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="auth-form">
                <?php echo getCSRFTokenField(); ?>
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Ghi nhớ tôi</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="auth-button">
                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                </button>
            </form>

            <!-- Social Login -->
            <div class="divider">Hoặc</div>

            <div class="social-login">
                <button type="button" class="social-btn">
                    <i class="fab fa-google"></i> Google
                </button>
                <button type="button" class="social-btn">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
        </div>
        </div>
    </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle i');
            
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
    </script>
</body>
</html>
