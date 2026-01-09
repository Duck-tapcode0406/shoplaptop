<?php
$page_title = 'Quản lý danh mục';
require_once 'includes/admin_header.php';

$success_message = '';
$error_message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action == 'add_supplier') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            if (empty($name)) {
                $error_message = 'Vui lòng nhập tên nhà cung cấp!';
            } else {
                $stmt = $conn->prepare("INSERT INTO supplier (name, description) VALUES (?, ?)");
                $stmt->bind_param('ss', $name, $description);
                if ($stmt->execute()) {
                    $success_message = 'Thêm nhà cung cấp thành công!';
                } else {
                    $error_message = 'Có lỗi xảy ra!';
                }
            }
        }
        
        if ($action == 'edit_supplier') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            $stmt = $conn->prepare("UPDATE supplier SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param('ssi', $name, $description, $id);
            if ($stmt->execute()) {
                $success_message = 'Cập nhật thành công!';
            }
        }
        
        if ($action == 'add_unit') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            if (empty($name)) {
                $error_message = 'Vui lòng nhập tên đơn vị!';
            } else {
                $stmt = $conn->prepare("INSERT INTO unit (name, description) VALUES (?, ?)");
                $stmt->bind_param('ss', $name, $description);
                if ($stmt->execute()) {
                    $success_message = 'Thêm đơn vị thành công!';
                }
            }
        }
        
        if ($action == 'edit_unit') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            $stmt = $conn->prepare("UPDATE unit SET name = ?, description = ? WHERE id = ?");
            $stmt->bind_param('ssi', $name, $description, $id);
            if ($stmt->execute()) {
                $success_message = 'Cập nhật thành công!';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete_supplier'])) {
    $id = intval($_GET['delete_supplier']);
    // Check if supplier has products
    $check = $conn->query("SELECT COUNT(*) as cnt FROM receipt r JOIN receipt_details rd ON r.id = rd.receipt_id WHERE r.supplier_id = $id")->fetch_assoc()['cnt'];
    if ($check > 0) {
        $error_message = 'Không thể xóa nhà cung cấp này vì có sản phẩm liên quan!';
    } else {
        $conn->query("DELETE FROM supplier WHERE id = $id");
        $success_message = 'Đã xóa nhà cung cấp!';
    }
}

if (isset($_GET['delete_unit'])) {
    $id = intval($_GET['delete_unit']);
    $check = $conn->query("SELECT COUNT(*) as cnt FROM product WHERE unit_id = $id")->fetch_assoc()['cnt'];
    if ($check > 0) {
        $error_message = 'Không thể xóa đơn vị này vì có sản phẩm đang sử dụng!';
    } else {
        $conn->query("DELETE FROM unit WHERE id = $id");
        $success_message = 'Đã xóa đơn vị!';
    }
}

// Get data
$suppliers = $conn->query("
    SELECT s.*, 
           (SELECT COUNT(DISTINCT rd.product_id) FROM receipt r JOIN receipt_details rd ON r.id = rd.receipt_id WHERE r.supplier_id = s.id) as product_count
    FROM supplier s 
    ORDER BY s.name
");

$units = $conn->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM product WHERE unit_id = u.id) as product_count
    FROM unit u 
    ORDER BY u.name
");
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Quản lý danh mục</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Danh mục</span>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary">
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

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- Suppliers -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-truck"></i> Nhà cung cấp (Thương hiệu)</h3>
            <button class="btn btn-sm btn-primary" onclick="showAddSupplier()">
                <i class="fas fa-plus"></i> Thêm
            </button>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Mô tả</th>
                    <th>SP</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $supplier['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($supplier['name']); ?></strong></td>
                    <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?php echo htmlspecialchars($supplier['description']); ?>
                    </td>
                    <td>
                        <span class="status-badge info"><?php echo $supplier['product_count']; ?></span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-sm btn-warning btn-icon" 
                                    onclick="editSupplier(<?php echo $supplier['id']; ?>, '<?php echo htmlspecialchars(addslashes($supplier['name'])); ?>', '<?php echo htmlspecialchars(addslashes($supplier['description'])); ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete_supplier=<?php echo $supplier['id']; ?>" 
                               class="btn btn-sm btn-danger btn-icon"
                               onclick="return confirmDelete('Xóa nhà cung cấp này?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Units -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-ruler"></i> Đơn vị tính</h3>
            <button class="btn btn-sm btn-primary" onclick="showAddUnit()">
                <i class="fas fa-plus"></i> Thêm
            </button>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Mô tả</th>
                    <th>SP</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($unit = $units->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $unit['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($unit['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($unit['description']); ?></td>
                    <td>
                        <span class="status-badge info"><?php echo $unit['product_count']; ?></span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-sm btn-warning btn-icon" 
                                    onclick="editUnit(<?php echo $unit['id']; ?>, '<?php echo htmlspecialchars(addslashes($unit['name'])); ?>', '<?php echo htmlspecialchars(addslashes($unit['description'])); ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete_unit=<?php echo $unit['id']; ?>" 
                               class="btn btn-sm btn-danger btn-icon"
                               onclick="return confirmDelete('Xóa đơn vị này?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Supplier Modal -->
<div id="supplierModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 450px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;" id="supplierModalTitle">Thêm nhà cung cấp</h3>
        <form method="POST">
            <?php echo getCSRFTokenField(); ?>
            <input type="hidden" name="action" id="supplierAction" value="add_supplier">
            <input type="hidden" name="id" id="supplierId">
            
            <div class="form-group">
                <label class="form-label">Tên nhà cung cấp <span style="color: red;">*</span></label>
                <input type="text" name="name" id="supplierName" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" id="supplierDesc" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeSupplierModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<!-- Add/Edit Unit Modal -->
<div id="unitModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 450px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;" id="unitModalTitle">Thêm đơn vị</h3>
        <form method="POST">
            <?php echo getCSRFTokenField(); ?>
            <input type="hidden" name="action" id="unitAction" value="add_unit">
            <input type="hidden" name="id" id="unitId">
            
            <div class="form-group">
                <label class="form-label">Tên đơn vị <span style="color: red;">*</span></label>
                <input type="text" name="name" id="unitName" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" id="unitDesc" class="form-control" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeUnitModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddSupplier() {
    document.getElementById('supplierModalTitle').textContent = 'Thêm nhà cung cấp';
    document.getElementById('supplierAction').value = 'add_supplier';
    document.getElementById('supplierId').value = '';
    document.getElementById('supplierName').value = '';
    document.getElementById('supplierDesc').value = '';
    document.getElementById('supplierModal').style.display = 'flex';
}

function editSupplier(id, name, desc) {
    document.getElementById('supplierModalTitle').textContent = 'Sửa nhà cung cấp';
    document.getElementById('supplierAction').value = 'edit_supplier';
    document.getElementById('supplierId').value = id;
    document.getElementById('supplierName').value = name;
    document.getElementById('supplierDesc').value = desc;
    document.getElementById('supplierModal').style.display = 'flex';
}

function closeSupplierModal() {
    document.getElementById('supplierModal').style.display = 'none';
}

function showAddUnit() {
    document.getElementById('unitModalTitle').textContent = 'Thêm đơn vị';
    document.getElementById('unitAction').value = 'add_unit';
    document.getElementById('unitId').value = '';
    document.getElementById('unitName').value = '';
    document.getElementById('unitDesc').value = '';
    document.getElementById('unitModal').style.display = 'flex';
}

function editUnit(id, name, desc) {
    document.getElementById('unitModalTitle').textContent = 'Sửa đơn vị';
    document.getElementById('unitAction').value = 'edit_unit';
    document.getElementById('unitId').value = id;
    document.getElementById('unitName').value = name;
    document.getElementById('unitDesc').value = desc;
    document.getElementById('unitModal').style.display = 'flex';
}

function closeUnitModal() {
    document.getElementById('unitModal').style.display = 'none';
}

// Close modals on outside click
document.getElementById('supplierModal').addEventListener('click', function(e) {
    if (e.target === this) closeSupplierModal();
});
document.getElementById('unitModal').addEventListener('click', function(e) {
    if (e.target === this) closeUnitModal();
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>



