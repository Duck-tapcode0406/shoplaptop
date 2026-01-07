<?php
$page_title = 'Thêm sản phẩm';
require_once 'includes/admin_header.php';

$error_message = '';
$success_message = '';

// Get units
$units = $conn->query("SELECT * FROM unit ORDER BY name");

// Get suppliers
$suppliers = $conn->query("SELECT id, name FROM supplier ORDER BY name");

// Get categories
$categories = $conn->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM category WHERE parent_id = c.id) as child_count
    FROM category c 
    WHERE c.parent_id IS NULL AND c.is_active = 1
    ORDER BY c.sort_order, c.name
");

// Get all categories for dropdown (including sub-categories)
$all_categories = $conn->query("
    SELECT c.id, c.name, c.parent_id, 
           COALESCE(p.name, '') as parent_name
    FROM category c
    LEFT JOIN category p ON c.parent_id = p.id
    WHERE c.is_active = 1
    ORDER BY c.parent_id, c.sort_order, c.name
");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        // Get form data
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $unit_id = intval($_POST['unit_id']);
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $sell_price = floatval($_POST['sell_price']);
        $purchase_price = floatval($_POST['purchase_price']);
        $supplier_id = intval($_POST['supplier_id']);
        $entry_date = $_POST['entry_date'];
        
        // Validate
        if (empty($name)) {
            $error_message = 'Vui lòng nhập tên sản phẩm!';
        } elseif ($sell_price <= 0) {
            $error_message = 'Giá bán phải lớn hơn 0!';
        } elseif ($purchase_price <= 0) {
            $error_message = 'Giá nhập phải lớn hơn 0!';
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert product
                if ($category_id) {
                    $stmt = $conn->prepare("INSERT INTO product (name, description, unit_id, category_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssii', $name, $description, $unit_id, $category_id);
                } else {
                    $stmt = $conn->prepare("INSERT INTO product (name, description, unit_id) VALUES (?, ?, ?)");
                    $stmt->bind_param('ssi', $name, $description, $unit_id);
                }
                $stmt->execute();
                $product_id = $conn->insert_id;
                
                // Insert product attributes if category has attributes
                if ($category_id && isset($_POST['attribute'])) {
                    foreach ($_POST['attribute'] as $attr_id => $attr_value) {
                        if (!empty($attr_value)) {
                            $attr_id = intval($attr_id);
                            $attr_value = trim($attr_value);
                            $attr_stmt = $conn->prepare("INSERT INTO product_attributes (product_id, attribute_id, attribute_value) VALUES (?, ?, ?)");
                            $attr_stmt->bind_param('iis', $product_id, $attr_id, $attr_value);
                            $attr_stmt->execute();
                        }
                    }
                }
                
                // Insert price
                $stmt = $conn->prepare("INSERT INTO price (product_id, price, datetime) VALUES (?, ?, NOW())");
                $stmt->bind_param('id', $product_id, $sell_price);
                $stmt->execute();
                
                // Create receipt
                $stmt = $conn->prepare("INSERT INTO receipt (supplier_id, datetime) VALUES (?, ?)");
                $stmt->bind_param('is', $supplier_id, $entry_date);
                $stmt->execute();
                $receipt_id = $conn->insert_id;
                
                // Insert configurations
                $total_quantity = 0;
                if (isset($_POST['color_name']) && is_array($_POST['color_name'])) {
                    $color_names = $_POST['color_name'];
                    $configuration_names = $_POST['configuration_name'];
                    $quantities = $_POST['quantity'];
                    
                    $stmt = $conn->prepare("INSERT INTO colors_configuration (product_id, color_name, configuration_name, quantity) VALUES (?, ?, ?, ?)");
                    
                    for ($i = 0; $i < count($color_names); $i++) {
                        $color = trim($color_names[$i]);
                        $config = trim($configuration_names[$i]);
                        $qty = intval($quantities[$i]);
                        
                        if (!empty($color) || !empty($config)) {
                            $total_quantity += $qty;
                            $stmt->bind_param('issi', $product_id, $color, $config, $qty);
                            $stmt->execute();
                        }
                    }
                }
                
                // Insert receipt details
                $stmt = $conn->prepare("INSERT INTO receipt_details (receipt_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iidi', $receipt_id, $product_id, $purchase_price, $total_quantity);
                $stmt->execute();
                
                // Handle images
                if (isset($_FILES['images']) && $_FILES['images']['error'][0] != UPLOAD_ERR_NO_FILE) {
                    $target_dir = "uploads/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO image (product_id, path, sort_order) VALUES (?, ?, ?)");
                    $sort_order = 1;
                    
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                            $original_name = $_FILES['images']['name'][$key];
                            $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                            
                            // Validate image
                            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            if (in_array($extension, $allowed)) {
                                $new_name = time() . '_' . $sort_order . '_' . uniqid() . '.' . $extension;
                                $target_file = $target_dir . $new_name;
                                
                                if (move_uploaded_file($tmp_name, $target_file)) {
                                    $stmt->bind_param('isi', $product_id, $target_file, $sort_order);
                                    $stmt->execute();
                                    $sort_order++;
                                }
                            }
                        }
                    }
                }
                
                $conn->commit();
                $success_message = 'Thêm sản phẩm thành công!';
                
                // Redirect to products list
                header("Location: products.php?success=1");
                exit();
                
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
        <h1 class="page-title">Thêm sản phẩm mới</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <a href="products.php">Sản phẩm</a>
            <span>/</span>
            <span>Thêm mới</span>
        </div>
    </div>
    <a href="products.php" class="btn btn-secondary">
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
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           placeholder="Nhập tên sản phẩm">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Danh mục</label>
                    <select name="category_id" id="categorySelect" class="form-control" onchange="loadCategoryAttributes()">
                        <option value="">-- Chọn danh mục --</option>
                        <?php while ($cat = $all_categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['parent_name'] ? $cat['parent_name'] . ' > ' . $cat['name'] : $cat['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small style="color: #999;">Chọn danh mục để hiển thị các thuộc tính tương ứng</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="5" 
                              placeholder="Nhập mô tả chi tiết sản phẩm"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <!-- Category Attributes (Dynamic Fields) -->
                <div id="categoryAttributes" style="display: none;">
                    <div class="card" style="background: #f8f9fa; margin-bottom: 20px;">
                        <div class="card-header">
                            <h3 class="card-title">Thông số kỹ thuật</h3>
                        </div>
                        <div id="attributesContainer">
                            <!-- Attributes will be loaded here via JavaScript -->
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Giá bán <span style="color: red;">*</span></label>
                        <input type="number" name="sell_price" class="form-control" required min="0"
                               value="<?php echo isset($_POST['sell_price']) ? $_POST['sell_price'] : ''; ?>"
                               placeholder="VND">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá nhập <span style="color: red;">*</span></label>
                        <input type="number" name="purchase_price" class="form-control" required min="0"
                               value="<?php echo isset($_POST['purchase_price']) ? $_POST['purchase_price'] : ''; ?>"
                               placeholder="VND">
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
                    <div class="configuration-item" style="display: grid; grid-template-columns: 1fr 1fr 100px 40px; gap: 10px; margin-bottom: 15px; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Màu sắc</label>
                            <input type="text" name="color_name[]" class="form-control" placeholder="Đen, Trắng, Xanh...">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Cấu hình</label>
                            <input type="text" name="configuration_name[]" class="form-control" placeholder="8GB/256GB, 16GB/512GB...">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Số lượng</label>
                            <input type="number" name="quantity[]" class="form-control" value="0" min="0">
                        </div>
                        <div></div>
                    </div>
                </div>
            </div>
            
            <!-- Images -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hình ảnh sản phẩm</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Chọn hình ảnh (có thể chọn nhiều)</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple 
                           onchange="previewImages(this)">
                    <small style="color: #999;">Định dạng: JPG, PNG, GIF, WebP. Tối đa 5MB mỗi ảnh.</small>
                </div>
                
                <div id="image-preview" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;"></div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin khác</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Đơn vị tính</label>
                    <select name="unit_id" class="form-control">
                        <?php while ($unit = $units->fetch_assoc()): ?>
                        <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nhà cung cấp</label>
                    <select name="supplier_id" class="form-control">
                        <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ngày nhập hàng</label>
                    <input type="date" name="entry_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            
            <div class="card">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                    <i class="fas fa-save"></i> Lưu sản phẩm
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Load category attributes via AJAX
function loadCategoryAttributes() {
    const categoryId = document.getElementById('categorySelect').value;
    const container = document.getElementById('categoryAttributes');
    const attributesContainer = document.getElementById('attributesContainer');
    
    if (!categoryId) {
        container.style.display = 'none';
        attributesContainer.innerHTML = '';
        return;
    }
    
    // Show loading
    attributesContainer.innerHTML = '<p style="text-align: center; padding: 20px; color: #999;">Đang tải thuộc tính...</p>';
    container.style.display = 'block';
    
    // Fetch attributes
    fetch('get_category_attributes.php?category_id=' + categoryId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.attributes.length > 0) {
                let html = '';
                data.attributes.forEach(attr => {
                    html += '<div class="form-group">';
                    html += `<label class="form-label">${attr.attribute_name}`;
                    if (attr.is_required == 1) {
                        html += ' <span style="color: red;">*</span>';
                    }
                    html += '</label>';
                    
                    if (attr.attribute_type === 'select' && attr.attribute_options) {
                        const options = JSON.parse(attr.attribute_options);
                        html += `<select name="attribute[${attr.id}]" class="form-control"`;
                        if (attr.is_required == 1) html += ' required';
                        html += '>';
                        html += '<option value="">-- Chọn --</option>';
                        options.forEach(opt => {
                            html += `<option value="${opt}">${opt}</option>`;
                        });
                        html += '</select>';
                    } else if (attr.attribute_type === 'textarea') {
                        html += `<textarea name="attribute[${attr.id}]" class="form-control" rows="3"`;
                        if (attr.is_required == 1) html += ' required';
                        html += ` placeholder="Nhập ${attr.attribute_name}"></textarea>`;
                    } else if (attr.attribute_type === 'number') {
                        html += `<input type="number" name="attribute[${attr.id}]" class="form-control"`;
                        if (attr.is_required == 1) html += ' required';
                        html += ` placeholder="Nhập ${attr.attribute_name}">`;
                    } else {
                        html += `<input type="text" name="attribute[${attr.id}]" class="form-control"`;
                        if (attr.is_required == 1) html += ' required';
                        html += ` placeholder="Nhập ${attr.attribute_name}">`;
                    }
                    html += '</div>';
                });
                attributesContainer.innerHTML = html;
            } else {
                attributesContainer.innerHTML = '<p style="text-align: center; padding: 20px; color: #999;">Danh mục này chưa có thuộc tính</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            attributesContainer.innerHTML = '<p style="text-align: center; padding: 20px; color: #e74c3c;">Có lỗi xảy ra khi tải thuộc tính</p>';
        });
}

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
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.cssText = 'position: relative; width: 100px; height: 100px;';
                div.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                    ${index === 0 ? '<span style="position: absolute; top: 5px; left: 5px; background: var(--primary); color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px;">Chính</span>' : ''}
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
}

// Load attributes on page load if category is already selected
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    if (categorySelect && categorySelect.value) {
        loadCategoryAttributes();
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>

