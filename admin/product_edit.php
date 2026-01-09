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
$images_result = $conn->query("SELECT * FROM image WHERE product_id = $product_id ORDER BY sort_order");
$images = $images_result ? $images_result : null;

// Get configurations
$configs_result = $conn->query("SELECT * FROM colors_configuration WHERE product_id = $product_id");
$configs = $configs_result ? $configs_result : null;

// Get all categories for dropdown (fetch before using in form)
$all_categories_query = $conn->query("
    SELECT c.id, c.name, c.parent_id, cp.name as parent_name
    FROM category c
    LEFT JOIN category cp ON c.parent_id = cp.id
    WHERE c.is_active = 1
    ORDER BY cp.name, c.name
");

// Store categories in array so we can reuse it
$all_categories = [];
while ($cat = $all_categories_query->fetch_assoc()) {
    $all_categories[] = $cat;
}

// Get units (fetch before using in form)
$units_query = $conn->query("SELECT * FROM unit ORDER BY name");

// Store units in array so we can reuse it
$units = [];
while ($unit = $units_query->fetch_assoc()) {
    $units[] = $unit;
}

// Get current product attributes (get only one value per category_attribute_id)
$product_attributes = [];
if (!empty($product['category_id'])) {
    $attr_result = $conn->query("
        SELECT pa.category_attribute_id, pa.attribute_value, ca.attribute_name, ca.attribute_type, ca.attribute_options
        FROM product_attributes pa
        JOIN category_attributes ca ON pa.category_attribute_id = ca.id
        WHERE pa.product_id = $product_id
        GROUP BY pa.category_attribute_id
        ORDER BY pa.id DESC
    ");
    if ($attr_result && $attr_result->num_rows > 0) {
        while ($attr = $attr_result->fetch_assoc()) {
            // Only store one value per category_attribute_id (the latest one)
            if (!isset($product_attributes[$attr['category_attribute_id']])) {
                $product_attributes[$attr['category_attribute_id']] = $attr;
            }
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $unit_id = intval($_POST['unit_id']);
        $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
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
                // Update product (including category_id)
                if ($category_id) {
                    $stmt = $conn->prepare("UPDATE product SET name = ?, description = ?, unit_id = ?, category_id = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('ssiii', $name, $description, $unit_id, $category_id, $product_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE product SET name = ?, description = ?, unit_id = ?, category_id = NULL WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('ssii', $name, $description, $unit_id, $product_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                
                // Delete old product attributes
                $conn->query("DELETE FROM product_attributes WHERE product_id = $product_id");
                
                // Insert new product attributes if category has attributes
                if ($category_id && isset($_POST['attribute']) && is_array($_POST['attribute'])) {
                    $attr_stmt = $conn->prepare("INSERT INTO product_attributes (product_id, category_attribute_id, attribute_value) VALUES (?, ?, ?)");
                    if ($attr_stmt) {
                        foreach ($_POST['attribute'] as $attr_id => $attr_value) {
                            if (!empty($attr_value)) {
                                $attr_id = intval($attr_id);
                                $attr_value = trim($attr_value);
                                $attr_stmt->bind_param('iis', $product_id, $attr_id, $attr_value);
                                $attr_stmt->execute();
                            }
                        }
                        $attr_stmt->close();
                    }
                }
                
                // Update price
                $stmt = $conn->prepare("UPDATE price SET price = ?, temporary_price = ?, discount_start = ?, discount_end = ? WHERE product_id = ?");
                if ($stmt) {
                    $stmt->bind_param('ddssi', $sell_price, $temporary_price, $discount_start, $discount_end, $product_id);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Delete old configurations and add new ones
                $conn->query("DELETE FROM colors_configuration WHERE product_id = $product_id");
                
                if (isset($_POST['color_name']) && is_array($_POST['color_name'])) {
                    $stmt = $conn->prepare("INSERT INTO colors_configuration (product_id, color_name, configuration_name, quantity) VALUES (?, ?, ?, ?)");
                    if ($stmt) {
                        foreach ($_POST['color_name'] as $i => $color) {
                            $config = $_POST['configuration_name'][$i] ?? '';
                            $qty = intval($_POST['quantity'][$i] ?? 0);
                            
                            if (!empty($color) || !empty($config)) {
                                $stmt->bind_param('issi', $product_id, $color, $config, $qty);
                                $stmt->execute();
                            }
                        }
                        $stmt->close();
                    }
                }
                
                // Handle deleted images
                if (isset($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $img_id) {
                        // Get image path
                        $img_result = $conn->query("SELECT path FROM image WHERE id = " . intval($img_id));
                        if ($img_result && $img_row = $img_result->fetch_assoc()) {
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
                    $max_order_result = $conn->query("SELECT MAX(sort_order) as max_order FROM image WHERE product_id = $product_id");
                    $max_order = 0;
                    if ($max_order_result && $max_row = $max_order_result->fetch_assoc()) {
                        $max_order = intval($max_row['max_order'] ?? 0);
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO image (product_id, path, sort_order) VALUES (?, ?, ?)");
                    if ($stmt) {
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
                        $stmt->close();
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
                
                $images_result = $conn->query("SELECT * FROM image WHERE product_id = $product_id ORDER BY sort_order");
                $images = $images_result ? $images_result : null;
                $configs_result = $conn->query("SELECT * FROM colors_configuration WHERE product_id = $product_id");
                $configs = $configs_result ? $configs_result : null;
                
                // Reload categories and product attributes
                $all_categories_query = $conn->query("
                    SELECT c.id, c.name, c.parent_id, cp.name as parent_name
                    FROM category c
                    LEFT JOIN category cp ON c.parent_id = cp.id
                    WHERE c.is_active = 1
                    ORDER BY cp.name, c.name
                ");
                
                $all_categories = [];
                while ($cat = $all_categories_query->fetch_assoc()) {
                    $all_categories[] = $cat;
                }
                
                // Reload units
                $units_query = $conn->query("SELECT * FROM unit ORDER BY name");
                $units = [];
                while ($unit = $units_query->fetch_assoc()) {
                    $units[] = $unit;
                }
                
                $product_attributes = [];
                if (!empty($product['category_id'])) {
                    $attr_result = $conn->query("
                        SELECT pa.category_attribute_id, pa.attribute_value, ca.attribute_name, ca.attribute_type, ca.attribute_options
                        FROM product_attributes pa
                        JOIN category_attributes ca ON pa.category_attribute_id = ca.id
                        WHERE pa.product_id = $product_id
                    ");
                    if ($attr_result && $attr_result->num_rows > 0) {
                        while ($attr = $attr_result->fetch_assoc()) {
                            $product_attributes[$attr['category_attribute_id']] = $attr;
                        }
                    }
                }
                
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
                        <label class="form-label">Danh mục</label>
                        <select name="category_id" id="category_id" class="form-control">
                            <option value="">-- Chọn danh mục --</option>
                            <?php 
                            foreach ($all_categories as $cat): 
                                $cat_name = $cat['parent_name'] ? $cat['parent_name'] . ' > ' . $cat['name'] : $cat['name'];
                                $selected = isset($product['category_id']) && $cat['id'] == $product['category_id'] ? 'selected' : '';
                            ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($cat_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đơn vị tính</label>
                        <select name="unit_id" class="form-control">
                            <?php foreach ($units as $unit): ?>
                            <option value="<?php echo $unit['id']; ?>" <?php echo $unit['id'] == $product['unit_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($unit['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giá bán <span style="color: red;">*</span></label>
                        <input type="number" name="sell_price" class="form-control" required min="0"
                               value="<?php echo $product['price']; ?>">
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
            
            <!-- Dynamic Attributes (based on category) -->
            <div class="card" id="attributesSection" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">Thuộc tính sản phẩm</h3>
                </div>
                <div id="attributesContainer"></div>
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
                    <?php if ($configs && $configs->num_rows > 0): ?>
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
                <?php if ($images && $images->num_rows > 0): ?>
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
// Product attributes data (current values)
const currentAttributes = <?php echo json_encode($product_attributes); ?>;
const currentCategoryId = <?php echo isset($product['category_id']) ? intval($product['category_id']) : 'null'; ?>;

// Track loaded attributes to prevent duplicates
const loadedAttributeIds = new Set();

// Load category attributes when category is selected
const categorySelect = document.getElementById('category_id');
if (categorySelect) {
    // Remove any existing event listeners by cloning and replacing
    const newCategorySelect = categorySelect.cloneNode(true);
    categorySelect.parentNode.replaceChild(newCategorySelect, categorySelect);
    
    newCategorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        const attributesSection = document.getElementById('attributesSection');
        const attributesContainer = document.getElementById('attributesContainer');
        
        if (categoryId) {
            // Clear previous loaded attributes
            loadedAttributeIds.clear();
            
            fetch(`get_category_attributes.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.attributes && data.attributes.length > 0) {
                        attributesContainer.innerHTML = '';
                        
                        // Use Map to track unique attributes by ID
                        const uniqueAttributes = new Map();
                        data.attributes.forEach(attr => {
                            if (!uniqueAttributes.has(attr.id)) {
                                uniqueAttributes.set(attr.id, attr);
                            }
                        });
                        
                        // Render only unique attributes
                        uniqueAttributes.forEach(attr => {
                            let inputHtml = '';
                            const required = attr.is_required ? 'required' : '';
                            const currentValue = currentAttributes[attr.id] ? currentAttributes[attr.id].attribute_value : '';
                            
                            if (attr.attribute_type === 'select' && attr.attribute_options) {
                                const options = JSON.parse(attr.attribute_options);
                                inputHtml = `<select name="attribute[${attr.id}]" class="form-control" ${required}>`;
                                inputHtml += `<option value="">-- Chọn --</option>`;
                                options.forEach(opt => {
                                    const selected = (currentValue === opt) ? 'selected' : '';
                                    inputHtml += `<option value="${opt}" ${selected}>${opt}</option>`;
                                });
                                inputHtml += `</select>`;
                            } else if (attr.attribute_type === 'textarea') {
                                inputHtml = `<textarea name="attribute[${attr.id}]" class="form-control" rows="3" ${required}>${escapeHtml(currentValue)}</textarea>`;
                            } else if (attr.attribute_type === 'number') {
                                inputHtml = `<input type="number" name="attribute[${attr.id}]" class="form-control" value="${currentValue}" ${required}>`;
                            } else {
                                inputHtml = `<input type="text" name="attribute[${attr.id}]" class="form-control" value="${escapeHtml(currentValue)}" ${required}>`;
                            }
                            
                            attributesContainer.innerHTML += `
                                <div class="form-group">
                                    <label class="form-label">
                                        ${escapeHtml(attr.attribute_name)} 
                                        ${attr.is_required ? '<span style="color: red;">*</span>' : ''}
                                    </label>
                                    ${inputHtml}
                                </div>
                            `;
                        });
                        attributesSection.style.display = 'block';
                } else {
                    attributesSection.style.display = 'none';
                    attributesContainer.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error loading attributes:', error);
                attributesSection.style.display = 'none';
                attributesContainer.innerHTML = '';
            });
    } else {
        attributesSection.style.display = 'none';
        attributesContainer.innerHTML = '';
    }
});

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load attributes on page load if category is selected
document.addEventListener('DOMContentLoaded', function() {
    if (currentCategoryId) {
        const categorySelect = document.getElementById('category_id');
        if (categorySelect && !loadedAttributeIds.size) {
            // Small delay to ensure event listener is attached
            setTimeout(() => {
                categorySelect.dispatchEvent(new Event('change'));
            }, 100);
        }
    }
});

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



