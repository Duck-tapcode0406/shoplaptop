<?php
$page_title = 'Quản lý nhà cung cấp';
require_once 'includes/admin_header.php';

$success_message = '';
$error_message = '';

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        $action = $_POST['action'] ?? '';
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $error_message = 'Vui lòng nhập tên nhà cung cấp!';
        } else {
            if ($action == 'add') {
                $stmt = $conn->prepare("INSERT INTO supplier (name, description) VALUES (?, ?)");
                $stmt->bind_param('ss', $name, $description);
                if ($stmt->execute()) {
                    $success_message = 'Thêm nhà cung cấp thành công!';
                }
            } elseif ($action == 'edit') {
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("UPDATE supplier SET name = ?, description = ? WHERE id = ?");
                $stmt->bind_param('ssi', $name, $description, $id);
                if ($stmt->execute()) {
                    $success_message = 'Cập nhật thành công!';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $check = $conn->query("SELECT COUNT(*) as cnt FROM receipt WHERE supplier_id = $id")->fetch_assoc()['cnt'];
    if ($check > 0) {
        $error_message = 'Không thể xóa nhà cung cấp này vì có phiếu nhập liên quan!';
    } else {
        $conn->query("DELETE FROM supplier WHERE id = $id");
        $success_message = 'Đã xóa nhà cung cấp!';
    }
}

// Get suppliers
$suppliers = $conn->query("
    SELECT s.*,
           (SELECT COUNT(DISTINCT rd.product_id) FROM receipt r JOIN receipt_details rd ON r.id = rd.receipt_id WHERE r.supplier_id = s.id) as product_count,
           (SELECT SUM(rd.quantity * rd.price) FROM receipt r JOIN receipt_details rd ON r.id = rd.receipt_id WHERE r.supplier_id = s.id) as total_value
    FROM supplier s
    ORDER BY s.name
");
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Quản lý nhà cung cấp</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Nhà cung cấp</span>
        </div>
    </div>
    <button class="btn btn-primary" onclick="showAddModal()">
        <i class="fas fa-plus"></i> Thêm nhà cung cấp
    </button>
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

<!-- Suppliers Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
    <div class="card" style="position: relative;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
            <div style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                <?php echo strtoupper(substr($supplier['name'], 0, 1)); ?>
            </div>
            <div>
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($supplier['name']); ?></h3>
                <small style="color: #999;">ID: #<?php echo $supplier['id']; ?></small>
            </div>
        </div>
        
        <p style="color: #666; font-size: 14px; margin-bottom: 15px; min-height: 40px;">
            <?php echo htmlspecialchars($supplier['description'] ?: 'Chưa có mô tả'); ?>
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <strong style="color: var(--primary);"><?php echo $supplier['product_count']; ?></strong>
                <small style="display: block; color: #999;">Sản phẩm</small>
            </div>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <strong style="color: var(--success);"><?php echo number_format($supplier['total_value'] ?? 0, 0, ',', '.'); ?></strong>
                <small style="display: block; color: #999;">Giá trị (VND)</small>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-sm btn-warning" style="flex: 1;" 
                    onclick="editSupplier(<?php echo $supplier['id']; ?>, '<?php echo htmlspecialchars(addslashes($supplier['name'])); ?>', '<?php echo htmlspecialchars(addslashes($supplier['description'])); ?>')">
                <i class="fas fa-edit"></i> Sửa
            </button>
            <a href="?delete=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-danger" style="flex: 1;"
               onclick="return confirmDelete('Xóa nhà cung cấp này?')">
                <i class="fas fa-trash"></i> Xóa
            </a>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- Add/Edit Modal -->
<div id="supplierModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 500px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;" id="modalTitle">Thêm nhà cung cấp</h3>
        <form method="POST">
            <?php echo getCSRFTokenField(); ?>
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="supplierId">
            
            <div class="form-group">
                <label class="form-label">Tên nhà cung cấp <span style="color: red;">*</span></label>
                <input type="text" name="name" id="supplierName" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Mô tả</label>
                <textarea name="description" id="supplierDesc" class="form-control" rows="4"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Thêm nhà cung cấp';
    document.getElementById('formAction').value = 'add';
    document.getElementById('supplierId').value = '';
    document.getElementById('supplierName').value = '';
    document.getElementById('supplierDesc').value = '';
    document.getElementById('supplierModal').style.display = 'flex';
}

function editSupplier(id, name, desc) {
    document.getElementById('modalTitle').textContent = 'Sửa nhà cung cấp';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('supplierId').value = id;
    document.getElementById('supplierName').value = name;
    document.getElementById('supplierDesc').value = desc;
    document.getElementById('supplierModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('supplierModal').style.display = 'none';
}

document.getElementById('supplierModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>

