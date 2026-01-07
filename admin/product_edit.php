<?php
$page_title = 'Sửa sản phẩm';
require_once 'includes/admin_header.php';

$error_message = '';
$success_message = '';

// Get product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    header('Location: products.php');
    exit();
}

// Get product info
$stmt = $conn->prepare("
    SELECT p.*, pr.price, pr.temporary_price, pr.discount_start, pr.discount_end
    FROM product p
    LEFT JOIN price pr ON p.id = pr.product_id
    WHERE p.id = ?
");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get product images
$images = $conn->query("SELECT * FROM image WHERE product_id = $product_id ORDER BY sort_order");

// Get configurations
$configs = $conn->query("SELECT * FROM colors_configuration WHERE product_id = $product_id");

// Get units
$units = $conn->query("SELECT * FROM unit ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $unit_id = intval($_POST['unit_id']);
        $sell_price = floatval($_POST['sell_price']);
        $temporary_price = !empty($_POST['temporary_price']) ? floatval($_POST['temporary_price']) : null;
        $discount_start = !empty($_POST['discount_start']) ? $_POST['discount_start'] : null;
        $discount_end = !empty($_POST['discount_end']) ? $_POST['discount_end'] : null;
        
        if (empty($name)) {
            $error_message = 'Vui lòng nhập tên sản phẩm!';
        } elseif ($sell_price <= 0) {
            $error_message = 'Giá bán phải lớn hơn 0!';
        } else {
            $conn->begin_transaction();
            
            try {
                // Update product
                $stmt = $conn->prepare("UPDATE product SET name = ?, description = ?, unit_id = ? WHERE id = ?");
                $stmt->bind_param('ssii', $name, $description, $unit_id, $product_id);
                $stmt->execute();
                
                // Update price
                $stmt = $conn->prepare("UPDATE price SET price = ?, temporary_price = ?, discount_start = ?, discount_end = ? WHERE product_id = ?");
                $stmt->bind_param('ddssi', $sell_price, $temporary_price, $discount_start, $discount_end, $product_id);
                $stmt->execute();
                
                // Delete old configurations and add new ones
                $conn->query("DELETE FROM colors_configuration WHERE product_id = $product_id");
                
                if (isset($_POST['color_name']) && is_array($_POST['color_name'])) {
                    $stmt = $conn->prepare("INSERT INTO colors_configuration (product_id, color_name, configuration_name, quantity) VALUES (?, ?, ?, ?)");
                    
                    foreach ($_POST['color_name'] as $i => $color) {
                        $config = $_POST['configuration_name'][$i] ?? '';
                        $qty = intval($_POST['quantity'][$i] ?? 0);
                        
                        if (!empty($color) || !empty($config)) {
                            $stmt->bind_param('issi', $product_id, $color, $config, $qty);
                            $stmt->execute();
                        }
                    }
                }
                
                // Handle deleted images
                if (isset($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $img_id) {
                        // Get image path
                        $img_result = $conn->query("SELECT path FROM image WHERE id = " . intval($img_id));
                        if ($img_row = $img_result->fetch_assoc()) {
                            if (file_exists($img_row['path'])) {
                                unlink($img_row['path']);
                            }
                        }
                        $conn->query("DELETE FROM image WHERE id = " . intval($img_id));
                    }
                }
                
                // Handle new images
                if (isset($_FILES['images']) && $_FILES['images']['error'][0] != UPLOAD_ERR_NO_FILE) {
                    $target_dir = "uploads/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    
                    // Get current max sort order
                    $max_order = $conn->query("SELECT MAX(sort_order) as max_order FROM image WHERE product_id = $product_id")->fetch_assoc()['max_order'] ?? 0;
                    
                    $stmt = $conn->prepare("INSERT INTO image (product_id, path, sort_order) VALUES (?, ?, ?)");
                    
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                            $original_name = $_FILES['images']['name'][$key];
                            $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                            
                            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            if (in_array($extension, $allowed)) {
                                $max_order++;
                                $new_name = time() . '_' . $max_order . '_' . uniqid() . '.' . $extension;
                                $target_file = $target_dir . $new_name;
                                
                                if (move_uploaded_file($tmp_name, $target_file)) {
                                    $stmt->bind_param('isi', $product_id, $target_file, $max_order);
                                    $stmt->execute();
                                }
                            }
                        }
                    }
                }
                
                $conn->commit();
                $success_message = 'Cập nhật sản phẩm thành công!';
                
                // Refresh product data
                $stmt = $conn->prepare("
                    SELECT p.*, pr.price, pr.temporary_price, pr.discount_start, pr.discount_end
                    FROM product p
                    LEFT JOIN price pr ON p.id = pr.product_id
                    WHERE p.id = ?
                ");
                $stmt->bind_param('i', $product_id);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
                
                $images = $conn->query("SELECT * FROM image WHERE product_id = $product_id ORDER BY sort_order");
                $configs = $conn->query("SELECT * FROM colors_configuration WHERE product_id = $product_id");
                
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Sửa sản phẩm</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <a href="products.php">Sản phẩm</a>
            <span>/</span>
            <span>Sửa #<?php echo $product_id; ?></span>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="../product_detail.php?id=<?php echo $product_id; ?>" target="_blank" class="btn btn-secondary">
            <i class="fas fa-eye"></i> Xem trang
        </a>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
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

<form method="POST" enctype="multipart/form-data">
    <?php echo getCSRFTokenField(); ?>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
        <!-- Main Info -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin sản phẩm</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tên sản phẩm <span style="color: red;">*</span></label>
                    <input type="text" name="name" class="form-control" required 
                           value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giá bán <span style="color: red;">*</span></label>
                        <input type="number" name="sell_price" class="form-control" required min="0"
                               value="<?php echo $product['price']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đơn vị tính</label>
                        <select name="unit_id" class="form-control">
                            <?php while ($unit = $units->fetch_assoc()): ?>
                            <option value="<?php echo $unit['id']; ?>" <?php echo $unit['id'] == $product['unit_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($unit['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Discount -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Khuyến mãi</h3>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" name="temporary_price" class="form-control" min="0"
                               value="<?php echo $product['temporary_price']; ?>"
                               placeholder="Để trống nếu không giảm giá">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bắt đầu</label>
                        <input type="datetime-local" name="discount_start" class="form-control"
                               value="<?php echo $product['discount_start'] ? date('Y-m-d\TH:i', strtotime($product['discount_start'])) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kết thúc</label>
                        <input type="datetime-local" name="discount_end" class="form-control"
                               value="<?php echo $product['discount_end'] ? date('Y-m-d\TH:i', strtotime($product['discount_end'])) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Configurations -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Màu sắc & Cấu hình</h3>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addConfiguration()">
                        <i class="fas fa-plus"></i> Thêm
                    </button>
                </div>
                
                <div id="configurations">
                    <?php if ($configs->num_rows > 0): ?>
                        <?php while ($config = $configs->fetch_assoc()): ?>
                        <div class="configuration-item" style="display: grid; grid-template-columns: 1fr 1fr 100px 40px; gap: 10px; margin-bottom: 15px; align-items: end;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Màu sắc</label>
                                <input type="text" name="color_name[]" class="form-control" 
                                       value="<?php echo htmlspecialchars($config['color_name']); ?>">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Cấu hình</label>
                                <input type="text" name="configuration_name[]" class="form-control" 
                                       value="<?php echo htmlspecialchars($config['configuration_name']); ?>">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Số lượng</label>
                                <input type="number" name="quantity[]" class="form-control" 
                                       value="<?php echo $config['quantity']; ?>" min="0">
                            </div>
                            <button type="button" class="btn btn-danger btn-icon" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="configuration-item" style="display: grid; grid-template-columns: 1fr 1fr 100px 40px; gap: 10px; margin-bottom: 15px; align-items: end;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Màu sắc</label>
                                <input type="text" name="color_name[]" class="form-control" placeholder="Đen, Trắng...">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Cấu hình</label>
                                <input type="text" name="configuration_name[]" class="form-control" placeholder="8GB/256GB...">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Số lượng</label>
                                <input type="number" name="quantity[]" class="form-control" value="0" min="0">
                            </div>
                            <div></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Images -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hình ảnh sản phẩm</h3>
                </div>
                
                <!-- Current Images -->
                <?php if ($images->num_rows > 0): ?>
                <div style="margin-bottom: 20px;">
                    <label class="form-label">Ảnh hiện tại (check để xóa)</label>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <?php while ($img = $images->fetch_assoc()): ?>
                        <div style="position: relative; width: 120px;">
                            <img src="<?php echo $img['path']; ?>" 
                                 style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #eee;">
                            <label style="display: flex; align-items: center; gap: 5px; margin-top: 5px; font-size: 12px;">
                                <input type="checkbox" name="delete_images[]" value="<?php echo $img['id']; ?>">
                                Xóa
                            </label>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Thêm ảnh mới</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple 
                           onchange="previewImages(this)">
                </div>
                
                <div id="image-preview" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;"></div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin</h3>
                </div>
                <p style="color: #999; font-size: 14px;">
                    <strong>ID:</strong> #<?php echo $product_id; ?><br>
                    <strong>Tạo lúc:</strong> -
                </p>
            </div>
            
            <div class="card">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; margin-bottom: 10px;">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <a href="products.php?delete=<?php echo $product_id; ?>" 
                   class="btn btn-danger" style="width: 100%; padding: 15px;"
                   onclick="return confirmDelete('Bạn có chắc muốn xóa sản phẩm này?')">
                    <i class="fas fa-trash"></i> Xóa sản phẩm
                </a>
            </div>
        </div>
    </div>
</form>

<script>
function addConfiguration() {
    const container = document.getElementById('configurations');
    const item = document.createElement('div');
    item.className = 'configuration-item';
    item.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr 100px 40px; gap: 10px; margin-bottom: 15px; align-items: end;';
    item.innerHTML = `
        <div class="form-group" style="margin-bottom: 0;">
            <input type="text" name="color_name[]" class="form-control" placeholder="Màu sắc">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <input type="text" name="configuration_name[]" class="form-control" placeholder="Cấu hình">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <input type="number" name="quantity[]" class="form-control" value="0" min="0">
        </div>
        <button type="button" class="btn btn-danger btn-icon" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(item);
}

function previewImages(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.cssText = 'width: 100px; height: 100px;';
                div.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>

