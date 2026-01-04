<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/helpers.php';
require_once 'includes/validator.php';
require_once 'includes/error_handler.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];

// Get user data using prepared statement
$stmt = $conn->prepare("SELECT username, familyname, firstname, email, phone, password FROM user WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    handleError("Không tìm thấy thông tin người dùng.", 404);
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF
    validateCSRFPost();
    
    // Validate and sanitize input
    $familyname_validation = Validator::username($_POST['familyname'] ?? '');
    $firstname_validation = Validator::username($_POST['firstname'] ?? '');
    $email_validation = Validator::email($_POST['email'] ?? '');
    $phone_validation = Validator::phone($_POST['phone'] ?? '');
    
    if (!$familyname_validation['valid']) {
        $message = $familyname_validation['message'];
        $message_type = 'error';
    } elseif (!$firstname_validation['valid']) {
        $message = $firstname_validation['message'];
        $message_type = 'error';
    } elseif (!$email_validation['valid']) {
        $message = $email_validation['message'];
        $message_type = 'error';
    } elseif (!$phone_validation['valid']) {
        $message = $phone_validation['message'];
        $message_type = 'error';
    } else {
        $familyname = Validator::sanitize($_POST['familyname']);
        $firstname = Validator::sanitize($_POST['firstname']);
        $email = Validator::sanitize($_POST['email'], 'email');
        $phone = Validator::sanitize($_POST['phone']);
        $password = $_POST['password'] ?? '';
        $old_password = $_POST['old_password'] ?? '';

        // Validate password if provided
        if (!empty($password)) {
            $password_validation = Validator::password($password, false);
            if (!$password_validation['valid']) {
                $message = $password_validation['message'];
                $message_type = 'error';
            } elseif (empty($old_password)) {
                $message = "Vui lòng nhập mật khẩu cũ để thay đổi mật khẩu mới!";
                $message_type = 'error';
            } elseif (!password_verify($old_password, $user['password'])) {
                $message = "Mật khẩu cũ không chính xác!";
                $message_type = 'error';
            } else {
                // Update with password using prepared statement
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE user SET familyname = ?, firstname = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $update_stmt->bind_param('sssssi', $familyname, $firstname, $email, $phone, $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $message = "Thông tin đã được cập nhật thành công!";
                    $message_type = 'success';
                    $user['familyname'] = $familyname;
                    $user['firstname'] = $firstname;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                } else {
                    error_log("Error updating user: " . $update_stmt->error);
                    $message = "Lỗi khi cập nhật thông tin!";
                    $message_type = 'error';
                }
            }
        } else {
            // Update without password using prepared statement
            $update_stmt = $conn->prepare("UPDATE user SET familyname = ?, firstname = ?, email = ?, phone = ? WHERE id = ?");
            $update_stmt->bind_param('ssssi', $familyname, $firstname, $email, $phone, $user_id);
            
            if ($update_stmt->execute()) {
                $message = "Thông tin đã được cập nhật thành công!";
                $message_type = 'success';
                $user['familyname'] = $familyname;
                $user['firstname'] = $firstname;
                $user['email'] = $email;
                $user['phone'] = $phone;
            } else {
                error_log("Error updating user: " . $update_stmt->error);
                $message = "Lỗi khi cập nhật thông tin!";
                $message_type = 'error';
            }
        }
    }
}
?>
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Tài Khoản - ModernShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .account-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .account-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: var(--space-3xl);
            margin-bottom: var(--space-5xl);
        }

        .account-sidebar {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            height: fit-content;
        }

        .account-menu {
            list-style: none;
        }

        .account-menu li {
            margin-bottom: 0;
        }

        .account-menu a {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-md);
            color: var(--text-primary);
            text-decoration: none;
            border-radius: var(--radius-md);
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .account-menu a:hover,
        .account-menu a.active {
            background-color: var(--bg-primary);
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .account-menu a i {
            width: 20px;
            text-align: center;
        }

        .account-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
        }

        .content-header {
            margin-bottom: var(--space-2xl);
            padding-bottom: var(--space-lg);
            border-bottom: 2px solid var(--border-color);
        }

        .content-header h2 {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin: 0;
        }

        .content-header i {
            color: var(--primary);
            font-size: 28px;
        }

        .form-group {
            margin-bottom: var(--space-lg);
        }

        .form-group label {
            display: block;
            margin-bottom: var(--space-sm);
            font-weight: var(--fw-bold);
            color: var(--text-primary);
        }

        .form-group input {
            width: 100%;
            padding: var(--space-sm) var(--space-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-lg);
        }

        .alert {
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }

        .alert-success {
            background-color: #F0FFF4;
            color: #22543D;
            border: 1px solid #9AE6B4;
        }

        .alert-error {
            background-color: #FFF5F5;
            color: #742A2A;
            border: 1px solid #FED7D7;
        }

        @media (max-width: 768px) {
            .account-layout {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="account-container">
        <div class="account-layout">
            <!-- Sidebar Menu -->
            <div class="account-sidebar">
                <h3 style="margin-bottom: var(--space-lg); display: flex; align-items: center; gap: var(--space-sm);">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </h3>
                <ul class="account-menu">
                    <li>
                        <a href="user.php" class="active">
                            <i class="fas fa-user"></i> Tài Khoản Của Tôi
                        </a>
                    </li>
                    <li>
                        <a href="history.php">
                            <i class="fas fa-history"></i> Lịch Sử Đơn Hàng
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-heart"></i> Danh Sách Yêu Thích
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-map-marker-alt"></i> Địa Chỉ
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-cog"></i> Cài Đặt
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Đăng Xuất
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="account-content">
                <div class="content-header">
                    <h2>
                        <i class="fas fa-user-edit"></i>
                        Chỉnh Sửa Thông Tin Cá Nhân
                    </h2>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                        <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="user.php">
                    <?php echo getCSRFTokenField(); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="familyname">
                                <i class="fas fa-user" style="margin-right: 8px;"></i>Họ
                            </label>
                            <input type="text" id="familyname" name="familyname" value="<?php echo htmlspecialchars($user['familyname']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="firstname">
                                <i class="fas fa-user" style="margin-right: 8px;"></i>Tên
                            </label>
                            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope" style="margin-right: 8px;"></i>Email
                            </label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">
                                <i class="fas fa-phone" style="margin-right: 8px;"></i>Số Điện Thoại
                            </label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                    </div>

                    <hr style="margin: var(--space-2xl) 0; border: none; border-top: 1px solid var(--border-color);">

                    <h3 style="margin-bottom: var(--space-lg);">
                        <i class="fas fa-lock" style="margin-right: var(--space-sm);"></i>Đổi Mật Khẩu
                    </h3>

                    <div class="form-group">
                        <label for="old_password">
                            <i class="fas fa-key" style="margin-right: 8px;"></i>Mật Khẩu Hiện Tại
                        </label>
                        <input type="password" id="old_password" name="old_password" placeholder="Nhập mật khẩu hiện tại (nếu thay đổi)">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-key" style="margin-right: 8px;"></i>Mật Khẩu Mới
                        </label>
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu mới (để trống nếu không thay đổi)">
                    </div>

                    <div style="display: flex; gap: var(--space-md); margin-top: var(--space-2xl);">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Lưu Thay Đổi
                        </button>
                        <a href="index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Quay Lại
                        </a>
                    </div>
                </form>

                <!-- Sản phẩm đã mua -->
                <hr style="margin: var(--space-3xl) 0; border: none; border-top: 2px solid var(--border-color);">
                
                <div class="content-header">
                    <h2>
                        <i class="fas fa-shopping-bag"></i>
                        Sản Phẩm Đã Mua
                    </h2>
                </div>

                <?php
                // Lấy sản phẩm đã mua
                $purchased_query = "
                    SELECT DISTINCT p.id, p.name, pr.price, img.path, od.status, o.datetime
                    FROM `order` o
                    JOIN order_details od ON o.id = od.order_id
                    JOIN product p ON od.product_id = p.id
                    LEFT JOIN price pr ON p.id = pr.product_id
                    LEFT JOIN image img ON p.id = img.product_id AND img.sort_order = 1
                    WHERE o.customer_id = $user_id AND od.status = 'paid'
                    ORDER BY o.datetime DESC
                    LIMIT 12
                ";
                $purchased_result = $conn->query($purchased_query);
                ?>

                <?php if ($purchased_result && $purchased_result->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: var(--space-lg); margin-top: var(--space-xl);">
                        <?php while ($product = $purchased_result->fetch_assoc()): ?>
                            <div style="background: var(--bg-primary); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); transition: transform 0.3s;">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                                    <div style="position: relative; padding-top: 75%; background: white; overflow: hidden;">
                                        <img src="admin/<?php echo htmlspecialchars($product['path'] ?: 'placeholder.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div style="padding: var(--space-md);">
                                        <h4 style="margin: 0 0 var(--space-sm) 0; font-size: var(--fs-body); color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </h4>
                                        <div style="color: var(--primary); font-weight: var(--fw-bold); font-size: var(--fs-h5);">
                                            <?php echo number_format($product['price'], 0, ',', '.'); ?> ₫
                                        </div>
                                        <div style="margin-top: var(--space-sm);">
                                            <span style="background: var(--success); color: white; padding: 4px 8px; border-radius: var(--radius-sm); font-size: var(--fs-tiny);">
                                                <i class="fas fa-check-circle"></i> Đã mua
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div style="text-align: center; margin-top: var(--space-xl);">
                        <a href="history.php" class="btn btn-primary">
                            <i class="fas fa-history"></i> Xem Tất Cả Đơn Hàng
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: var(--space-5xl) var(--space-md); color: var(--text-secondary);">
                        <i class="fas fa-shopping-bag" style="font-size: 4rem; color: var(--text-light); margin-bottom: var(--space-lg);"></i>
                        <h3>Chưa có sản phẩm nào</h3>
                        <p>Bạn chưa mua sản phẩm nào. Hãy bắt đầu mua sắm ngay!</p>
                        <a href="index.php" class="btn btn-primary" style="margin-top: var(--space-lg);">
                            <i class="fas fa-shopping-cart"></i> Mua Sắm Ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>

<?php
$conn->close();
?>
