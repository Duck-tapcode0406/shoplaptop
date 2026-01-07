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
$stmt = $conn->prepare("SELECT username, familyname, firstname, email, phone, password, avatar FROM user WHERE id = ?");
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
    <title>Thông Tin Tài Khoản - DNQDH Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .account-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .account-layout {
            margin-bottom: var(--space-5xl);
        }

        .account-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            max-width: 900px;
            margin: 0 auto;
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
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="account-container">
        <div class="account-layout">
            <!-- Main Content -->
            <div class="account-content">
                <?php if (!empty($message)): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showNotification('<?php echo $message_type; ?>', 
                                '<?php echo $message_type === 'success' ? 'Thành công' : 'Lỗi'; ?>', 
                                '<?php echo addslashes($message); ?>', 
                                5000);
                        });
                    </script>
                <?php endif; ?>

                <!-- Thay Đổi Ảnh Đại Diện - Đặt lên trên cùng -->
                <div class="content-header">
                    <h2>
                        <i class="fas fa-user-circle"></i>
                        Ảnh Đại Diện
                    </h2>
                </div>

                <div class="avatar-upload-section">
                    <div class="avatar-preview-container">
                        <div class="avatar-preview">
                            <?php 
                            $avatar_path = !empty($user['avatar']) ? 'uploads/avatars/' . htmlspecialchars($user['avatar']) : 'images/avatar-default.png';
                            ?>
                            <img id="avatar-preview-img" src="<?php echo $avatar_path; ?>" alt="Ảnh đại diện" onerror="this.onerror=null; this.src='images/avatar-default.png';">
                            <div class="avatar-overlay">
                                <i class="fas fa-camera"></i>
                                <span>Thay đổi ảnh</span>
                            </div>
                        </div>
                    </div>

                    <form id="avatar-upload-form" enctype="multipart/form-data" style="margin-top: var(--space-lg);">
                        <div class="form-group" style="text-align: center;">
                            <label for="avatar-input" class="btn btn-secondary" style="cursor: pointer; display: inline-flex; align-items: center; gap: var(--space-sm);">
                                <i class="fas fa-upload"></i> Chọn Ảnh
                            </label>
                            <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                            <button type="button" id="upload-avatar-btn" class="btn btn-primary" style="display: none; margin-left: var(--space-sm);">
                                <i class="fas fa-save"></i> Lưu Ảnh
                            </button>
                            <button type="button" id="cancel-avatar-btn" class="btn btn-secondary" style="display: none; margin-left: var(--space-sm);" onclick="cancelAvatarUpload()">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                        </div>
                        <small style="color: var(--text-secondary); font-size: var(--fs-small); display: block; margin-top: var(--space-sm); text-align: center;">
                            <i class="fas fa-info-circle"></i> Định dạng: JPG, PNG, GIF. Kích thước tối đa: 5MB
                        </small>
                    </form>
                </div>

                <style>
                    .avatar-upload-section {
                        max-width: 500px;
                        margin: 0 auto var(--space-3xl) auto;
                    }

                    .avatar-preview-container {
                        display: flex;
                        justify-content: center;
                        margin-bottom: var(--space-lg);
                    }

                    .avatar-preview {
                        position: relative;
                        width: 150px;
                        height: 150px;
                        border-radius: 50%;
                        overflow: hidden;
                        border: 4px solid var(--border-color);
                        cursor: pointer;
                        transition: all 0.3s;
                    }

                    .avatar-preview:hover {
                        border-color: var(--primary);
                        transform: scale(1.05);
                    }

                    .avatar-preview img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                    }

                    .avatar-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.6);
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        opacity: 0;
                        transition: opacity 0.3s;
                        color: white;
                    }

                    .avatar-preview:hover .avatar-overlay {
                        opacity: 1;
                    }

                    .avatar-overlay i {
                        font-size: 2rem;
                        margin-bottom: var(--space-xs);
                    }

                    .avatar-overlay span {
                        font-size: var(--fs-small);
                        font-weight: var(--fw-semibold);
                    }
                </style>

                <hr style="margin: var(--space-3xl) 0; border: none; border-top: 2px solid var(--border-color);">

                <div class="content-header">
                    <h2>
                        <i class="fas fa-user-edit"></i>
                        Chỉnh Sửa Thông Tin Cá Nhân
                    </h2>
                </div>

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

    <script>
        // Preview avatar khi chọn file
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview-img').src = e.target.result;
                    document.getElementById('upload-avatar-btn').style.display = 'inline-flex';
                    document.getElementById('cancel-avatar-btn').style.display = 'inline-flex';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Hủy upload avatar
        function cancelAvatarUpload() {
            document.getElementById('avatar-input').value = '';
            document.getElementById('upload-avatar-btn').style.display = 'none';
            document.getElementById('cancel-avatar-btn').style.display = 'none';
            // Khôi phục ảnh cũ
            const oldAvatar = '<?php echo !empty($user['avatar']) ? "uploads/avatars/" . htmlspecialchars($user['avatar']) : "images/avatar-default.png"; ?>';
            document.getElementById('avatar-preview-img').src = oldAvatar;
        }

        // Upload avatar
        document.getElementById('upload-avatar-btn')?.addEventListener('click', function() {
            const fileInput = document.getElementById('avatar-input');
            if (!fileInput.files || !fileInput.files[0]) {
                if (typeof showWarning === 'function') {
                    showWarning('Cảnh báo', 'Vui lòng chọn ảnh trước khi upload');
                } else {
                    alert('Vui lòng chọn ảnh trước khi upload');
                }
                return;
            }

            const formData = new FormData();
            formData.append('avatar', fileInput.files[0]);
            formData.append('action', 'upload_avatar');

            // Hiển thị loading
            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang upload...';

            fetch('api/upload_avatar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (data.success) {
                    if (typeof showSuccess === 'function') {
                        showSuccess('Thành công', data.message || 'Đã cập nhật ảnh đại diện thành công!');
                    } else {
                        alert(data.message || 'Đã cập nhật ảnh đại diện thành công!');
                    }
                    // Cập nhật ảnh preview
                    if (data.imageUrl) {
                        document.getElementById('avatar-preview-img').src = data.imageUrl;
                    }
                    // Ẩn nút upload và cancel
                    document.getElementById('upload-avatar-btn').style.display = 'none';
                    document.getElementById('cancel-avatar-btn').style.display = 'none';
                    fileInput.value = '';
                } else {
                    if (typeof showError === 'function') {
                        showError('Lỗi', data.message || 'Không thể upload ảnh đại diện');
                    } else {
                        alert(data.message || 'Không thể upload ảnh đại diện');
                    }
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                console.error('Error:', error);
                if (typeof showError === 'function') {
                    showError('Lỗi', 'Đã xảy ra lỗi khi upload ảnh');
                } else {
                    alert('Đã xảy ra lỗi khi upload ảnh');
                }
            });
        });

        // Click vào avatar preview để chọn file
        document.querySelector('.avatar-preview')?.addEventListener('click', function() {
            document.getElementById('avatar-input').click();
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
