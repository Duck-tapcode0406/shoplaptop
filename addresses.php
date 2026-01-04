<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Check if shipping_addresses table exists
$addresses_table_exists = false;
$check_table = $conn->query("SHOW TABLES LIKE 'shipping_addresses'");
if ($check_table && $check_table->num_rows > 0) {
    $addresses_table_exists = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $ward = isset($_POST['ward']) ? trim($_POST['ward']) : '';
            $district = isset($_POST['district']) ? trim($_POST['district']) : '';
            $city = isset($_POST['city']) ? trim($_POST['city']) : '';
            $postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
            $is_default = isset($_POST['is_default']) ? 1 : 0;

            if (empty($full_name) || empty($phone) || empty($address) || empty($city)) {
                $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
                $message_type = 'error';
            } elseif ($addresses_table_exists) {
                // If setting as default, unset other defaults
                if ($is_default) {
                    $unset_default = $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?");
                    $unset_default->bind_param('i', $user_id);
                    $unset_default->execute();
                }

                if ($_POST['action'] === 'add') {
                    $stmt = $conn->prepare("INSERT INTO shipping_addresses (user_id, full_name, phone, address, ward, district, city, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('isssssssi', $user_id, $full_name, $phone, $address, $ward, $district, $city, $postal_code, $is_default);
                } else {
                    $address_id = intval($_POST['address_id']);
                    $stmt = $conn->prepare("UPDATE shipping_addresses SET full_name = ?, phone = ?, address = ?, ward = ?, district = ?, city = ?, postal_code = ?, is_default = ? WHERE id = ? AND user_id = ?");
                    $stmt->bind_param('sssssssii', $full_name, $phone, $address, $ward, $district, $city, $postal_code, $is_default, $address_id, $user_id);
                }

                if ($stmt->execute()) {
                    $message = $_POST['action'] === 'add' ? 'Đã thêm địa chỉ thành công!' : 'Đã cập nhật địa chỉ thành công!';
                    $message_type = 'success';
                } else {
                    $message = 'Có lỗi xảy ra: ' . $conn->error;
                    $message_type = 'error';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $address_id = intval($_POST['address_id']);
            $stmt = $conn->prepare("DELETE FROM shipping_addresses WHERE id = ? AND user_id = ?");
            $stmt->bind_param('ii', $address_id, $user_id);
            if ($stmt->execute()) {
                $message = 'Đã xóa địa chỉ thành công!';
                $message_type = 'success';
            } else {
                $message = 'Có lỗi xảy ra: ' . $conn->error;
                $message_type = 'error';
            }
        } elseif ($_POST['action'] === 'set_default') {
            $address_id = intval($_POST['address_id']);
            // Unset all defaults
            $unset_default = $conn->prepare("UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ?");
            $unset_default->bind_param('i', $user_id);
            $unset_default->execute();
            // Set new default
            $set_default = $conn->prepare("UPDATE shipping_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
            $set_default->bind_param('ii', $address_id, $user_id);
            if ($set_default->execute()) {
                $message = 'Đã đặt làm địa chỉ mặc định!';
                $message_type = 'success';
            }
        }
    }
}

// Fetch addresses
$addresses = [];
if ($addresses_table_exists) {
    $addresses_query = "SELECT * FROM shipping_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
    $stmt = $conn->prepare($addresses_query);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Địa Chỉ Giao Hàng - DuckShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .addresses-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .addresses-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-3xl);
            padding-bottom: var(--space-lg);
            border-bottom: 2px solid var(--border-color);
        }

        .addresses-header h1 {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin: 0;
        }

        .addresses-header i {
            color: #E74C3C;
            font-size: 32px;
        }

        .addresses-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: var(--space-3xl);
        }

        .addresses-list {
            display: grid;
            gap: var(--space-lg);
        }

        .address-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            border: 2px solid var(--border-color);
            transition: all var(--transition-normal);
            position: relative;
        }

        .address-card:hover {
            box-shadow: var(--shadow-md);
        }

        .address-card.default {
            border-color: #E74C3C;
            background: #fff5f5;
        }

        .address-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--space-md);
        }

        .address-card-name {
            font-size: var(--fs-h5);
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-xs);
        }

        .address-card-phone {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .address-card-badge {
            background: #E74C3C;
            color: white;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-full);
            font-size: var(--fs-tiny);
            font-weight: var(--fw-bold);
        }

        .address-card-body {
            margin-bottom: var(--space-md);
            line-height: 1.6;
            color: var(--text-primary);
        }

        .address-card-actions {
            display: flex;
            gap: var(--space-sm);
        }

        .address-form-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 100px;
        }

        .address-form-card h3 {
            margin-bottom: var(--space-lg);
            color: var(--text-primary);
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
            color: #E74C3C;
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

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .empty-addresses {
            text-align: center;
            padding: var(--space-5xl);
        }

        .empty-addresses-icon {
            font-size: 80px;
            color: var(--text-light);
            margin-bottom: var(--space-lg);
        }

        @media (max-width: 1024px) {
            .addresses-layout {
                grid-template-columns: 1fr;
            }

            .address-form-card {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="addresses-container">
        <div class="addresses-header">
            <h1>
                <i class="fas fa-map-marker-alt"></i>
                Địa Chỉ Giao Hàng
            </h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: var(--space-lg);">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!$addresses_table_exists): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Hệ thống quản lý địa chỉ đang được thiết lập. Vui lòng chạy file <code>database_wishlist_addresses.sql</code> để kích hoạt tính năng này.
            </div>
        <?php else: ?>
            <div class="addresses-layout">
                <!-- Addresses List -->
                <div class="addresses-list">
                    <?php if (count($addresses) > 0): ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                <div class="address-card-header">
                                    <div>
                                        <div class="address-card-name">
                                            <?php echo htmlspecialchars($address['full_name']); ?>
                                            <?php if ($address['is_default']): ?>
                                                <span class="address-card-badge">Mặc định</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="address-card-phone">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($address['phone']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="address-card-body">
                                    <p style="margin: 0;">
                                        <?php echo htmlspecialchars($address['address']); ?><br>
                                        <?php if ($address['ward']): ?>
                                            <?php echo htmlspecialchars($address['ward']); ?>, 
                                        <?php endif; ?>
                                        <?php if ($address['district']): ?>
                                            <?php echo htmlspecialchars($address['district']); ?>, 
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($address['city']); ?>
                                        <?php if ($address['postal_code']): ?>
                                            <br>Mã bưu chính: <?php echo htmlspecialchars($address['postal_code']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="address-card-actions">
                                    <?php if (!$address['is_default']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="set_default">
                                            <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-star"></i> Đặt mặc định
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn btn-primary btn-sm" onclick="editAddress(<?php echo htmlspecialchars(json_encode($address)); ?>)">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-addresses">
                            <div class="empty-addresses-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h2>Chưa có địa chỉ nào</h2>
                            <p style="color: var(--text-secondary); margin-bottom: var(--space-3xl);">Thêm địa chỉ giao hàng để mua sắm dễ dàng hơn!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Address Form -->
                <div class="address-form-card">
                    <h3 id="form-title">
                        <i class="fas fa-plus-circle"></i> Thêm Địa Chỉ Mới
                    </h3>
                    <div style="margin-bottom: var(--space-lg); padding: var(--space-md); background: #f8f9fa; border-radius: var(--radius-md); border-left: 4px solid #E74C3C;">
                        <p style="margin: 0 0 var(--space-sm) 0; font-weight: var(--fw-bold); font-size: var(--fs-small);">
                            <i class="fas fa-map-marker-alt"></i> Xác Nhận Vị Trí Tự Động
                        </p>
                        <button type="button" id="verify-location-btn" class="btn btn-primary btn-sm" style="width: 100%;">
                            <i class="fas fa-crosshairs"></i> Xác Nhận Vị Trí Hiện Tại
                        </button>
                    </div>
                    <form method="POST" id="address-form">
                        <input type="hidden" name="action" value="add" id="form-action">
                        <input type="hidden" name="address_id" id="address_id">

                        <div class="form-group">
                            <label for="full_name">Họ và tên <span class="required">*</span></label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Địa chỉ <span class="required">*</span></label>
                            <input type="text" id="address" name="address" required placeholder="Số nhà, tên đường">
                        </div>

                        <div class="form-group">
                            <label for="ward">Phường/Xã</label>
                            <input type="text" id="ward" name="ward" placeholder="Phường/Xã">
                        </div>

                        <div class="form-group">
                            <label for="district">Quận/Huyện</label>
                            <input type="text" id="district" name="district" placeholder="Quận/Huyện">
                        </div>

                        <div class="form-group">
                            <label for="city">Thành phố/Tỉnh <span class="required">*</span></label>
                            <input type="text" id="city" name="city" required placeholder="Thành phố/Tỉnh">
                        </div>

                        <div class="form-group">
                            <label for="postal_code">Mã bưu chính</label>
                            <input type="text" id="postal_code" name="postal_code" placeholder="Mã bưu chính">
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_default" name="is_default">
                                <label for="is_default" style="margin: 0; cursor: pointer;">Đặt làm địa chỉ mặc định</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Lưu Địa Chỉ
                        </button>
                        <button type="button" class="btn btn-secondary btn-block" id="cancel-edit" style="display: none; margin-top: var(--space-sm);" onclick="resetForm()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/location-verification.js"></script>
    <script>
        // Khởi tạo Location Verification
        window.locationVerificationInstance = new LocationVerification({
            apiEndpoint: 'api/update_location.php',
            onSuccess: function(data) {
                console.log('Đã cập nhật địa chỉ:', data);
                // Reload trang để hiển thị địa chỉ mới
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            onError: function(message) {
                console.error('Lỗi:', message);
            },
            onLocationFound: function(place, location) {
                // Tự động điền form với thông tin địa chỉ
                if (place.address) {
                    document.getElementById('address').value = place.address;
                }
                if (place.title && !place.address) {
                    document.getElementById('address').value = place.title;
                }
                
                // Trích xuất thành phố và quận từ địa chỉ
                if (place.address) {
                    const addressParts = place.address.split(',');
                    if (addressParts.length > 1) {
                        document.getElementById('district').value = addressParts[addressParts.length - 2].trim();
                    }
                    if (addressParts.length > 0) {
                        document.getElementById('city').value = addressParts[addressParts.length - 1].trim();
                    }
                }
                
                // Điền số điện thoại nếu có
                if (place.phone) {
                    document.getElementById('phone').value = place.phone;
                }
            }
        });

        // Xử lý sự kiện click nút xác nhận vị trí
        document.getElementById('verify-location-btn')?.addEventListener('click', function() {
            window.locationVerificationInstance.verifyAndUpdate();
        });

        function editAddress(address) {
            document.getElementById('form-title').innerHTML = '<i class="fas fa-edit"></i> Sửa Địa Chỉ';
            document.getElementById('form-action').value = 'edit';
            document.getElementById('address_id').value = address.id;
            document.getElementById('full_name').value = address.full_name;
            document.getElementById('phone').value = address.phone;
            document.getElementById('address').value = address.address;
            document.getElementById('ward').value = address.ward || '';
            document.getElementById('district').value = address.district || '';
            document.getElementById('city').value = address.city;
            document.getElementById('postal_code').value = address.postal_code || '';
            document.getElementById('is_default').checked = address.is_default == 1;
            document.getElementById('cancel-edit').style.display = 'block';
            
            // Scroll to form
            document.querySelector('.address-form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function resetForm() {
            document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm Địa Chỉ Mới';
            document.getElementById('form-action').value = 'add';
            document.getElementById('address_id').value = '';
            document.getElementById('address-form').reset();
            document.getElementById('cancel-edit').style.display = 'none';
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>


