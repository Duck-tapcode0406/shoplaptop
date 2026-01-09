<?php
/**
 * Handle POST request FIRST before including header
 * This prevents "headers already sent" error
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/csrf.php';
    
    // Check admin login
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
    
    // Check if user is admin
    $user_id = $_SESSION['user_id'];
    $admin_check = $conn->prepare("SELECT is_admin FROM user WHERE id = ?");
    $admin_check->bind_param('i', $user_id);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    $admin_data_check = $admin_result ? $admin_result->fetch_assoc() : null;
    
    if (!$admin_data_check || $admin_data_check['is_admin'] != 1) {
        header('Location: ../index.php');
        exit();
    }
    
    // Validate CSRF
    if (validateCSRFPost()) {
        // Get form data
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $unit_id = intval($_POST['unit_id'] ?? 0);
        $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $sell_price = floatval($_POST['sell_price'] ?? 0);
        $purchase_price = floatval($_POST['purchase_price'] ?? 0);
        $supplier_id = intval($_POST['supplier_id'] ?? 0);
        $entry_date = $_POST['entry_date'] ?? date('Y-m-d H:i:s');
        
        // Validate
        $redirect_error = '';
        if (empty($name)) {
            $redirect_error = 'name_empty';
        } elseif ($sell_price <= 0) {
            $redirect_error = 'price_invalid';
        } elseif ($purchase_price <= 0) {
            $redirect_error = 'purchase_price_invalid';
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
                if ($category_id && isset($_POST['attribute']) && is_array($_POST['attribute'])) {
                    foreach ($_POST['attribute'] as $attr_id => $attr_value) {
                        if (!empty($attr_value)) {
                            $attr_id = intval($attr_id);
                            $attr_value = trim($attr_value);
                            $attr_stmt = $conn->prepare("INSERT INTO product_attributes (product_id, category_attribute_id, attribute_value) VALUES (?, ?, ?)");
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
                    $configuration_names = $_POST['configuration_name'] ?? [];
                    $quantities = $_POST['quantity'] ?? [];
                    
                    $stmt = $conn->prepare("INSERT INTO colors_configuration (product_id, color_name, configuration_name, quantity) VALUES (?, ?, ?, ?)");
                    
                    for ($i = 0; $i < count($color_names); $i++) {
                        $color = trim($color_names[$i] ?? '');
                        $config = trim($configuration_names[$i] ?? '');
                        $qty = intval($quantities[$i] ?? 0);
                        
                        if (!empty($color) || !empty($config)) {
                            $total_quantity += $qty;
                            $stmt->bind_param('issi', $product_id, $color, $config, $qty);
                            $stmt->execute();
                        }
                    }
                }
                
                // Insert receipt details
                if ($total_quantity > 0) {
                    $stmt = $conn->prepare("INSERT INTO receipt_details (receipt_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('iidi', $receipt_id, $product_id, $purchase_price, $total_quantity);
                    $stmt->execute();
                }
                
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
                
                // Redirect to products list
                header("Location: products.php?success=1");
                exit();
                
            } catch (Exception $e) {
                $conn->rollback();
                header("Location: product_add.php?error=" . urlencode($e->getMessage()));
                exit();
            }
        }
        
        if ($redirect_error) {
            header("Location: product_add.php?error=" . $redirect_error);
            exit();
        }
    } else {
        header("Location: product_add.php?error=csrf_invalid");
        exit();
    }
}

// Now include header and show form
$page_title = 'Thêm sản phẩm';
require_once 'includes/admin_header.php';

$error_message = '';
$success_message = '';

// Get error from URL
if (isset($_GET['error'])) {
    $error_code = $_GET['error'];
    switch ($error_code) {
        case 'name_empty':
            $error_message = 'Vui lòng nhập tên sản phẩm!';
            break;
        case 'price_invalid':
            $error_message = 'Giá bán phải lớn hơn 0!';
            break;
        case 'purchase_price_invalid':
            $error_message = 'Giá nhập phải lớn hơn 0!';
            break;
        case 'csrf_invalid':
            $error_message = 'Token không hợp lệ!';
            break;
        default:
            $error_message = htmlspecialchars($error_code);
    }
}

// Get success message
if (isset($_GET['success'])) {
    $success_message = 'Thêm sản phẩm thành công!';
}

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
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Thêm sản phẩm</h1>
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

<form method="POST" enctype="multipart/form-data" id="productForm">
    <?php echo getCSRFTokenField(); ?>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
        <!-- Main Form -->
        <div>
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin cơ bản</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tên sản phẩm <span style="color: red;">*</span></label>
                    <input type="text" name="name" class="form-control" required
                           placeholder="Nhập tên sản phẩm">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Nhập mô tả sản phẩm"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Danh mục</label>
                        <select name="category_id" id="category_id" class="form-control">
                            <option value="">-- Chọn danh mục --</option>
                            <?php 
                            $current_parent = null;
                            while ($cat = $all_categories->fetch_assoc()): 
                                $cat_name = $cat['parent_name'] ? $cat['parent_name'] . ' > ' . $cat['name'] : $cat['name'];
                            ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat_name); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Đơn vị tính <span style="color: red;">*</span></label>
                        <select name="unit_id" class="form-control" required>
                            <option value="">-- Chọn đơn vị --</option>
                            <?php while ($unit = $units->fetch_assoc()): ?>
                            <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
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
            
            <!-- Images -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hình ảnh</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Chọn hình ảnh</label>
                    <input type="file" name="images[]" class="form-control" multiple 
                           accept="image/*" id="imageInput">
                    <small style="color: #999;">Có thể chọn nhiều ảnh (JPG, PNG, GIF, WEBP)</small>
                    <div id="imagePreview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; margin-top: 15px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <!-- Price & Supplier -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Giá & Nhà cung cấp</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Giá bán (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" name="sell_price" class="form-control" required min="0" step="1000"
                           placeholder="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Giá nhập (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" name="purchase_price" class="form-control" required min="0" step="1000"
                           placeholder="0">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nhà cung cấp <span style="color: red;">*</span></label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Chọn nhà cung cấp --</option>
                        <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ngày nhập hàng</label>
                    <input type="datetime-local" name="entry_date" class="form-control"
                           value="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
            </div>
            
            <!-- Configuration -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cấu hình & Số lượng</h3>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addConfiguration()">
                        <i class="fas fa-plus"></i> Thêm
                    </button>
                </div>
                
                <div id="configurationsContainer">
                    <div class="config-row" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="color_name[]" class="form-control" placeholder="Màu sắc">
                        <input type="text" name="configuration_name[]" class="form-control" placeholder="Cấu hình">
                        <input type="number" name="quantity[]" class="form-control" placeholder="SL" min="0" value="0" style="width: 80px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card" style="margin-top: 25px;">
        <div style="display: flex; gap: 15px; justify-content: flex-end;">
            <a href="products.php" class="btn btn-secondary">Hủy</a>
            <button type="submit" class="btn btn-primary" style="min-width: 150px;">
                <i class="fas fa-save"></i> Lưu sản phẩm
            </button>
        </div>
    </div>
</form>

<script>
// Load category attributes when category is selected
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    const attributesSection = document.getElementById('attributesSection');
    const attributesContainer = document.getElementById('attributesContainer');
    
    if (categoryId) {
        fetch(`get_category_attributes.php?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    attributesContainer.innerHTML = '';
                    data.forEach(attr => {
                        let inputHtml = '';
                        const required = attr.is_required ? 'required' : '';
                        
                        if (attr.attribute_type === 'select' && attr.attribute_options) {
                            const options = JSON.parse(attr.attribute_options);
                            inputHtml = `<select name="attribute[${attr.id}]" class="form-control" ${required}>
                                <option value="">-- Chọn --</option>`;
                            options.forEach(opt => {
                                inputHtml += `<option value="${opt}">${opt}</option>`;
                            });
                            inputHtml += `</select>`;
                        } else if (attr.attribute_type === 'textarea') {
                            inputHtml = `<textarea name="attribute[${attr.id}]" class="form-control" rows="3" ${required}></textarea>`;
                        } else if (attr.attribute_type === 'number') {
                            inputHtml = `<input type="number" name="attribute[${attr.id}]" class="form-control" ${required}>`;
                        } else {
                            inputHtml = `<input type="text" name="attribute[${attr.id}]" class="form-control" ${required}>`;
                        }
                        
                        attributesContainer.innerHTML += `
                            <div class="form-group">
                                <label class="form-label">
                                    ${attr.attribute_name} 
                                    ${attr.is_required ? '<span style="color: red;">*</span>' : ''}
                                </label>
                                ${inputHtml}
                            </div>
                        `;
                    });
                    attributesSection.style.display = 'block';
                } else {
                    attributesSection.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading attributes:', error);
                attributesSection.style.display = 'none';
            });
    } else {
        attributesSection.style.display = 'none';
    }
});

// Image preview
document.getElementById('imageInput').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.position = 'relative';
            div.innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px;">
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

// Add configuration row
function addConfiguration() {
    const container = document.getElementById('configurationsContainer');
    const div = document.createElement('div');
    div.className = 'config-row';
    div.style.cssText = 'display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 10px; margin-bottom: 10px;';
    div.innerHTML = `
        <input type="text" name="color_name[]" class="form-control" placeholder="Màu sắc">
        <input type="text" name="configuration_name[]" class="form-control" placeholder="Cấu hình">
        <input type="number" name="quantity[]" class="form-control" placeholder="SL" min="0" value="0" style="width: 80px;">
        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
