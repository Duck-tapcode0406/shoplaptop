<?php
$page_title = 'Quản lý khuyến mãi';
require_once 'includes/admin_header.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        $product_id = intval($_POST['product_id']);
        $temporary_price = floatval($_POST['temporary_price']);
        $discount_start = $_POST['discount_start'];
        $discount_end = $_POST['discount_end'];
        
        // Validate dates
        $now = date('Y-m-d H:i:s');
        $start_time = strtotime($discount_start);
        $end_time = strtotime($discount_end);
        $current_time = strtotime($now);
        
        if ($product_id && $temporary_price > 0) {
            if ($start_time < $current_time) {
                $error_message = 'Thời gian bắt đầu phải từ bây giờ trở đi!';
            } elseif ($end_time <= $start_time) {
                $error_message = 'Thời gian kết thúc phải sau thời gian bắt đầu!';
            } else {
                $stmt = $conn->prepare("UPDATE price SET temporary_price = ?, discount_start = ?, discount_end = ? WHERE product_id = ?");
                $stmt->bind_param('dssi', $temporary_price, $discount_start, $discount_end, $product_id);
                if ($stmt->execute()) {
                    $success_message = 'Cập nhật khuyến mãi thành công!';
                }
            }
        }
    }
}

// Handle remove discount
if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    $conn->query("UPDATE price SET temporary_price = NULL, discount_start = NULL, discount_end = NULL WHERE product_id = $product_id");
    $success_message = 'Đã xóa khuyến mãi!';
}

// Get products with discounts
$discounted = $conn->query("
    SELECT p.id, p.name, pr.price, pr.temporary_price, pr.discount_start, pr.discount_end, i.path as image
    FROM product p
    JOIN price pr ON p.id = pr.product_id
    LEFT JOIN image i ON p.id = i.product_id AND i.sort_order = 1
    WHERE pr.temporary_price IS NOT NULL
    ORDER BY pr.discount_end DESC
");

// Get all products for dropdown
$all_products = $conn->query("
    SELECT p.id, p.name, pr.price
    FROM product p
    JOIN price pr ON p.id = pr.product_id
    ORDER BY p.name
");
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Quản lý khuyến mãi</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Khuyến mãi</span>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Thêm khuyến mãi
        </button>
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

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <?php
    $now = date('Y-m-d H:i:s');
    $active_count = $conn->query("SELECT COUNT(*) as cnt FROM price WHERE temporary_price IS NOT NULL AND discount_start <= '$now' AND discount_end >= '$now'")->fetch_assoc()['cnt'];
    $upcoming_count = $conn->query("SELECT COUNT(*) as cnt FROM price WHERE temporary_price IS NOT NULL AND discount_start > '$now'")->fetch_assoc()['cnt'];
    $expired_count = $conn->query("SELECT COUNT(*) as cnt FROM price WHERE temporary_price IS NOT NULL AND discount_end < '$now'")->fetch_assoc()['cnt'];
    ?>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-fire"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $active_count; ?></h3>
            <p>Đang khuyến mãi</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $upcoming_count; ?></h3>
            <p>Sắp diễn ra</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-history"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $expired_count; ?></h3>
            <p>Đã kết thúc</p>
        </div>
    </div>
</div>

<!-- Discounts Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách khuyến mãi</h3>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Giá gốc</th>
                <th>Giá KM</th>
                <th>Giảm</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($discounted->num_rows > 0): ?>
                <?php while ($item = $discounted->fetch_assoc()): ?>
                <?php
                $now = date('Y-m-d H:i:s');
                $is_active = $item['discount_start'] <= $now && $item['discount_end'] >= $now;
                $is_upcoming = $item['discount_start'] > $now;
                $discount_percent = round((($item['price'] - $item['temporary_price']) / $item['price']) * 100);
                ?>
                <tr>
                    <td>
                        <div class="product-cell">
                            <img src="<?php echo $item['image'] ? $item['image'] : '../images/no-image.png'; ?>" 
                                 class="product-img" alt="">
                            <span><?php echo htmlspecialchars(mb_substr($item['name'], 0, 40)); ?></span>
                        </div>
                    </td>
                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                    <td style="color: var(--danger); font-weight: 600;">
                        <?php echo number_format($item['temporary_price'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <span class="status-badge danger">-<?php echo $discount_percent; ?>%</span>
                    </td>
                    <td>
                        <small>
                            <?php echo date('d/m/Y H:i', strtotime($item['discount_start'])); ?><br>
                            đến <?php echo date('d/m/Y H:i', strtotime($item['discount_end'])); ?>
                        </small>
                    </td>
                    <td>
                        <?php if ($is_active): ?>
                            <span class="status-badge success">Đang diễn ra</span>
                        <?php elseif ($is_upcoming): ?>
                            <span class="status-badge info">Sắp diễn ra</span>
                        <?php else: ?>
                            <span class="status-badge warning">Đã kết thúc</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn btn-sm btn-warning btn-icon" 
                                    onclick="editDiscount(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>', <?php echo $item['price']; ?>, <?php echo $item['temporary_price']; ?>, '<?php echo $item['discount_start']; ?>', '<?php echo $item['discount_end']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger btn-icon"
                               onclick="return confirmDelete('Xóa khuyến mãi này?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        <i class="fas fa-percent" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                        <p style="color: #999;">Chưa có khuyến mãi nào</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Modal -->
<div id="discountModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 500px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;" id="modalTitle">Thêm khuyến mãi</h3>
        <form method="POST">
            <?php echo getCSRFTokenField(); ?>
            
            <div class="form-group">
                <label class="form-label">Sản phẩm <span style="color: red;">*</span></label>
                <select name="product_id" id="productSelect" class="form-control" required onchange="updatePrice()">
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php while ($p = $all_products->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>">
                        <?php echo htmlspecialchars($p['name']); ?> - <?php echo number_format($p['price'], 0, ',', '.'); ?>đ
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Giá gốc</label>
                <input type="text" id="originalPrice" class="form-control" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Giá khuyến mãi <span style="color: red;">*</span></label>
                <input type="number" name="temporary_price" id="tempPrice" class="form-control" required min="0">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Bắt đầu <span style="color: red;">*</span></label>
                    <input type="datetime-local" name="discount_start" id="discountStart" class="form-control" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                    <small style="color: #999;">Chỉ được chọn từ ngày giờ hiện tại</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Kết thúc <span style="color: red;">*</span></label>
                    <input type="datetime-local" name="discount_end" id="discountEnd" class="form-control" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                    <small style="color: #999;">Phải sau thời gian bắt đầu</small>
                </div>
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
    document.getElementById('modalTitle').textContent = 'Thêm khuyến mãi';
    document.getElementById('productSelect').value = '';
    document.getElementById('originalPrice').value = '';
    document.getElementById('tempPrice').value = '';
    // Set min to current datetime
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const nowStr = now.toISOString().slice(0, 16);
    document.getElementById('discountStart').value = '';
    document.getElementById('discountStart').min = nowStr;
    document.getElementById('discountEnd').value = '';
    document.getElementById('discountEnd').min = nowStr;
    document.getElementById('discountModal').style.display = 'flex';
}

function editDiscount(id, name, price, tempPrice, start, end) {
    document.getElementById('modalTitle').textContent = 'Sửa khuyến mãi: ' + name;
    document.getElementById('productSelect').value = id;
    document.getElementById('originalPrice').value = price.toLocaleString('vi-VN') + 'đ';
    document.getElementById('tempPrice').value = tempPrice;
    
    // Set dates
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const nowStr = now.toISOString().slice(0, 16);
    
    const startDate = start.replace(' ', 'T').slice(0, 16);
    const endDate = end.replace(' ', 'T').slice(0, 16);
    
    document.getElementById('discountStart').value = startDate;
    document.getElementById('discountStart').min = nowStr;
    document.getElementById('discountEnd').value = endDate;
    document.getElementById('discountEnd').min = startDate; // End must be after start
    
    document.getElementById('discountModal').style.display = 'flex';
}

// Update end date min when start date changes
document.addEventListener('DOMContentLoaded', function() {
    const startInput = document.getElementById('discountStart');
    const endInput = document.getElementById('discountEnd');
    
    if (startInput && endInput) {
        startInput.addEventListener('change', function() {
            endInput.min = this.value || this.min;
            if (endInput.value && endInput.value < this.value) {
                endInput.value = this.value;
            }
        });
    }
});

function updatePrice() {
    const select = document.getElementById('productSelect');
    const option = select.options[select.selectedIndex];
    const price = option.dataset.price;
    if (price) {
        document.getElementById('originalPrice').value = parseInt(price).toLocaleString('vi-VN') + 'đ';
    }
    
    // Reset min dates when product changes
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const nowStr = now.toISOString().slice(0, 16);
    document.getElementById('discountStart').min = nowStr;
    document.getElementById('discountEnd').min = nowStr;
}

function closeModal() {
    document.getElementById('discountModal').style.display = 'none';
}

document.getElementById('discountModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>

