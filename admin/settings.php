<?php
$page_title = 'Cài đặt';
require_once 'includes/admin_header.php';

$success_message = '';

// For now, just display settings info
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Cài đặt hệ thống</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Cài đặt</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- Shop Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-store"></i> Thông tin cửa hàng</h3>
        </div>
        
        <div class="form-group">
            <label class="form-label">Tên cửa hàng</label>
            <input type="text" class="form-control" value="DNQDH Shop" readonly>
        </div>
        
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" value="daiducka123@gmail.com" readonly>
        </div>
        
        <div class="form-group">
            <label class="form-label">Điện thoại</label>
            <input type="text" class="form-control" value="0393340406" readonly>
        </div>
        
        <div class="form-group">
            <label class="form-label">Địa chỉ</label>
            <textarea class="form-control" rows="2" readonly>77 Nguyễn Huệ, Thành phố Huế</textarea>
        </div>
        
        <p style="color: #999; font-size: 12px;">
            <i class="fas fa-info-circle"></i> Để thay đổi thông tin, vui lòng chỉnh sửa file <code>includes/footer.php</code>
        </p>
    </div>
    
    <!-- System Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-server"></i> Thông tin hệ thống</h3>
        </div>
        
        <table class="data-table">
            <tbody>
                <tr>
                    <td><strong>PHP Version</strong></td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td><strong>MySQL Version</strong></td>
                    <td><?php echo $conn->server_info; ?></td>
                </tr>
                <tr>
                    <td><strong>Server</strong></td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Document Root</strong></td>
                    <td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
                </tr>
                <tr>
                    <td><strong>Max Upload Size</strong></td>
                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                </tr>
                <tr>
                    <td><strong>Max POST Size</strong></td>
                    <td><?php echo ini_get('post_max_size'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Database Stats -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-database"></i> Thống kê Database</h3>
        </div>
        
        <?php
        $tables = ['user', 'product', 'order', 'order_details', 'price', 'image', 'reviews', 'wishlist', 'supplier'];
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bảng</th>
                    <th>Số bản ghi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables as $table): ?>
                <?php
                $result = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
                $count = $result ? $result->fetch_assoc()['cnt'] : 0;
                ?>
                <tr>
                    <td><code><?php echo $table; ?></code></td>
                    <td><span class="status-badge info"><?php echo number_format($count); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bolt"></i> Thao tác nhanh</h3>
        </div>
        
        <div style="display: grid; gap: 15px;">
            <a href="../index.php" target="_blank" class="btn btn-secondary" style="justify-content: flex-start;">
                <i class="fas fa-external-link-alt"></i> Xem trang chủ
            </a>
            <a href="products.php" class="btn btn-secondary" style="justify-content: flex-start;">
                <i class="fas fa-box"></i> Quản lý sản phẩm
            </a>
            <a href="orders.php?status=pending" class="btn btn-secondary" style="justify-content: flex-start;">
                <i class="fas fa-clock"></i> Đơn hàng chờ xử lý
            </a>
            <a href="discounts.php" class="btn btn-secondary" style="justify-content: flex-start;">
                <i class="fas fa-percent"></i> Thiết lập khuyến mãi
            </a>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>

