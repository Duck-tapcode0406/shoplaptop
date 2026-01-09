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
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div>
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
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bolt"></i> Thao tác nhanh</h3>
        </div>
        
        <div style="display: grid; gap: 15px;">
            <a href="../index.php" class="btn btn-secondary" style="justify-content: flex-start;">
                <i class="fas fa-store"></i> Xem trang chủ (chế độ admin)
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

