<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';
require_once 'includes/error_handler.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF
    validateCSRFPost();
    
    // Get and validate input
    $input_username = Validator::sanitize($_POST['username'] ?? '');
    $input_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $familyname = Validator::sanitize($_POST['familyname'] ?? '');
    $firstname = Validator::sanitize($_POST['firstname'] ?? '');
    $phone = Validator::sanitize($_POST['phone'] ?? '');
    $email = Validator::sanitize($_POST['email'] ?? '', 'email');

    // Validate all fields
    $username_validation = Validator::username($input_username);
    $password_validation = Validator::password($input_password, false);
    $email_validation = Validator::email($email);
    $phone_validation = Validator::phone($phone);
    
    if (empty($familyname) || empty($firstname)) {
        $error_message = "Vui lòng điền đầy đủ các trường bắt buộc!";
    } elseif (!$username_validation['valid']) {
        $error_message = $username_validation['message'];
    } elseif (!$password_validation['valid']) {
        $error_message = $password_validation['message'];
    } elseif ($input_password !== $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp!";
    } elseif (!$email_validation['valid']) {
        $error_message = $email_validation['message'];
    } elseif (!$phone_validation['valid']) {
        $error_message = $phone_validation['message'];
    } else {
        // Check if username exists using prepared statement
        $check_stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
        $check_stmt->bind_param('s', $input_username);
        $check_stmt->execute();
        $result_check = $check_stmt->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = "Tên đăng nhập đã tồn tại!";
        } else {
            // Check if email exists
            $email_check_stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
            $email_check_stmt->bind_param('s', $email);
            $email_check_stmt->execute();
            $email_result = $email_check_stmt->get_result();
            
            if ($email_result->num_rows > 0) {
                $error_message = "Email đã được sử dụng!";
            } else {
                // Insert user using prepared statement
                $hashed_password = password_hash($input_password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO user (username, password, familyname, firstname, phone, email) 
                                              VALUES (?, ?, ?, ?, ?, ?)");
                $insert_stmt->bind_param('ssssss', $input_username, $hashed_password, $familyname, $firstname, $phone, $email);

                if ($insert_stmt->execute()) {
                    $user_id = $conn->insert_id;
                    $role_id = 2;
                    
                    // Insert user role using prepared statement
                    $role_stmt = $conn->prepare("INSERT INTO user_role (role_id, user_id) VALUES (?, ?)");
                    $role_stmt->bind_param('ii', $role_id, $user_id);

                    if ($role_stmt->execute()) {
                        $success_message = "Đăng ký thành công! Chuyển hướng đến trang đăng nhập...";
                        header("refresh:2;url=login.php");
                    } else {
                        error_log("Error assigning role: " . $role_stmt->error);
                        $error_message = "Lỗi khi gán quyền!";
                    }
                } else {
                    error_log("Error registering user: " . $insert_stmt->error);
                    $error_message = "Lỗi khi đăng ký. Vui lòng thử lại.";
                }
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
    <title>Đăng Ký - ModernShop</title>
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
            max-width: 480px;
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
            color: var(--secondary);
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-md);
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            margin-bottom: var(--space-lg);
        }

        .form-group label {
            display: block;
            margin-bottom: var(--space-sm);
            font-weight: var(--fw-medium);
            color: var(--text-primary);
            font-size: var(--fs-small);
        }

        .form-group label .required {
            color: var(--error);
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

        .password-strength {
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
            margin-top: var(--space-xs);
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            background-color: var(--error);
            transition: width 0.3s ease;
        }

        .password-strength-bar.fair {
            background-color: var(--warning);
            width: 50%;
        }

        .password-strength-bar.good {
            background-color: var(--secondary);
            width: 75%;
        }

        .password-strength-bar.strong {
            background-color: var(--success);
            width: 100%;
        }

        .terms {
            display: flex;
            align-items: flex-start;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
            font-size: var(--fs-small);
        }

        .terms input {
            margin-top: 4px;
            cursor: pointer;
        }

        .terms a {
            color: var(--secondary);
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

        .auth-button:disabled {
            background: #e9ecef;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .auth-footer {
            text-align: center;
            font-size: var(--fs-small);
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--secondary);
            font-weight: var(--fw-medium);
            text-decoration: none;
        }

        .auth-footer a:hover {
            color: var(--secondary-dark);
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

        @media (max-width: 576px) {
            .auth-card {
                padding: var(--space-lg);
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <!-- Left Side - Image & Quote -->
        <div class="split-left">
            <div class="split-left-content">
                <h1>Tham Gia ModernShop Ngay Hôm Nay</h1>
                <p>"Khởi đầu hành trình công nghệ của bạn với chúng tôi. Tạo tài khoản và khám phá thế giới công nghệ tuyệt vời."</p>
            </div>
        </div>

        <!-- Right Side - Register Form -->
        <div class="split-right">
            <div class="auth-container">
                <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1 class="auth-title">Tạo Tài Khoản</h1>
                <p class="auth-subtitle">Tham gia ModernShop ngay hôm nay</p>
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

            <!-- Register Form -->
            <form method="POST" class="auth-form" onsubmit="return validateForm()">
                <?php echo getCSRFTokenField(); ?>
                <!-- Username -->
                <div class="form-group form-row full">
                    <label for="username">Tên đăng nhập <span class="required">*</span></label>
                    <input type="text" id="username" name="username" placeholder="Chọn tên đăng nhập" required>
                </div>

                <!-- Name Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="familyname">Họ <span class="required">*</span></label>
                        <input type="text" id="familyname" name="familyname" placeholder="Họ của bạn" required>
                    </div>
                    <div class="form-group">
                        <label for="firstname">Tên <span class="required">*</span></label>
                        <input type="text" id="firstname" name="firstname" placeholder="Tên của bạn" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group form-row full">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="Địa chỉ email của bạn" required>
                </div>

                <!-- Phone -->
                <div class="form-group form-row full">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" placeholder="Số điện thoại của bạn">
                </div>

                <!-- Password -->
                <div class="form-group form-row full">
                    <label for="password">Mật khẩu <span class="required">*</span></label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required onchange="checkPasswordStrength()">
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strength-bar"></div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group form-row full">
                    <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                </div>

                <!-- Terms -->
                <label class="terms">
                    <input type="checkbox" name="terms" required>
                    <span>Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách bảo mật</a></span>
                </label>

                <!-- Submit Button -->
                <button type="submit" class="auth-button">
                    <i class="fas fa-user-plus"></i> Tạo Tài Khoản
                </button>
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
            </div>
        </div>
    </div>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strength-bar');
            
            let strength = 0;
            if (password.length >= 6) strength += 1;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[!@#$%^&*]/.test(password)) strength += 1;

            strengthBar.className = 'password-strength-bar';
            if (strength === 1) strengthBar.classList.add('fair');
            if (strength === 2) strengthBar.classList.add('fair');
            if (strength === 3) strengthBar.classList.add('good');
            if (strength === 4) strengthBar.classList.add('strong');
        }

        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }

            if (password.length < 6) {
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }

            return true;
        }
    </script>
            </div>
        </div>
    </div>
</body>
</html>
