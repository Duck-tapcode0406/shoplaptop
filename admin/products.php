<?php
$page_title = 'Quản lý sản phẩm';
require_once 'includes/admin_header.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Delete related records first
    $conn->query("DELETE FROM image WHERE product_id = $delete_id");
    $conn->query("DELETE FROM price WHERE product_id = $delete_id");
    $conn->query("DELETE FROM colors_configuration WHERE product_id = $delete_id");
    $conn->query("DELETE FROM receipt_details WHERE product_id = $delete_id");
    $conn->query("DELETE FROM order_details WHERE product_id = $delete_id");
    $conn->query("DELETE FROM wishlist WHERE product_id = $delete_id");
    $conn->query("DELETE FROM reviews WHERE product_id = $delete_id");
    
    // Delete product
    $conn->query("DELETE FROM product WHERE id = $delete_id");
    
    $success_message = "Đã xóa sản phẩm thành công!";
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search & Filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND p.name LIKE '%$search%'";
}
if ($category) {
    $where .= " AND p.category_id = $category";
}

$order_by = "ORDER BY p.id DESC";
if ($sort == 'price_asc') $order_by = "ORDER BY pr.price ASC";
if ($sort == 'price_desc') $order_by = "ORDER BY pr.price DESC";
if ($sort == 'name') $order_by = "ORDER BY p.name ASC";

// Get total
$total_result = $conn->query("SELECT COUNT(DISTINCT p.id) as cnt FROM product p $where");
$total = $total_result->fetch_assoc()['cnt'];
$total_pages = ceil($total / $per_page);

// Get products
$products = $conn->query("
    SELECT p.id, p.name, p.description, 
           pr.price, pr.temporary_price, pr.discount_start, pr.discount_end,
           i.path as image,
           rd.quantity as stock,
           (SELECT SUM(quantity) FROM order_details WHERE product_id = p.id AND status = 'paid') as sold
    FROM product p
    LEFT JOIN price pr ON p.id = pr.product_id
    LEFT JOIN image i ON p.id = i.product_id
    LEFT JOIN receipt_details rd ON p.id = rd.product_id
    $where
    GROUP BY p.id
    $order_by
    LIMIT $per_page OFFSET $offset
");

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category_id FROM product WHERE category_id IS NOT NULL");
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Quản lý sản phẩm</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Sản phẩm</span>
        </div>
    </div>
    <a href="product_add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm sản phẩm
    </a>
</div>

<?php if (isset($success_message)): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="margin-bottom: 25px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm..." 
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <select name="sort" class="form-control" style="width: auto;">
            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
            <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Theo tên</option>
        </select>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-redo"></i> Reset
        </a>
    </form>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách sản phẩm (<?php echo $total; ?> sản phẩm)</h3>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-sm btn-success" onclick="exportExcel()" title="Xuất file Excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-sm btn-info" onclick="printTable()" title="In danh sách">
                <i class="fas fa-print"></i> In
            </button>
        </div>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 80px;">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Đã bán</th>
                    <th>Trạng thái</th>
                    <th style="width: 150px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products->num_rows > 0): ?>
                    <?php while ($product = $products->fetch_assoc()): ?>
                    <?php
                    $now = date('Y-m-d H:i:s');
                    $is_on_sale = !empty($product['discount_start']) && !empty($product['discount_end']) && 
                                  $now >= $product['discount_start'] && $now <= $product['discount_end'];
                    $current_price = $is_on_sale ? $product['temporary_price'] : $product['price'];
                    ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <img src="<?php echo $product['image'] ? $product['image'] : '../images/no-image.png'; ?>" 
                                 class="product-img" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars(mb_substr($product['name'], 0, 50)); ?></strong>
                            <?php if (strlen($product['name']) > 50): ?>...<?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_on_sale): ?>
                                <span style="text-decoration: line-through; color: #999; font-size: 12px;">
                                    <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                                </span><br>
                                <span style="color: #e74c3c; font-weight: bold;">
                                    <?php echo number_format($product['temporary_price'], 0, ',', '.'); ?>đ
                                </span>
                            <?php else: ?>
                                <strong><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</strong>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $stock = $product['stock'] ?? 0;
                            $stock_class = $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger');
                            ?>
                            <span class="status-badge <?php echo $stock_class; ?>">
                                <?php echo number_format($stock); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($product['sold'] ?? 0); ?></td>
                        <td>
                            <?php if ($is_on_sale): ?>
                                <span class="status-badge danger">Đang giảm giá</span>
                            <?php elseif ($stock > 0): ?>
                                <span class="status-badge success">Còn hàng</span>
                            <?php else: ?>
                                <span class="status-badge danger">Hết hàng</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="product_edit.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-warning btn-icon" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../product_detail.php?id=<?php echo $product['id']; ?>" 
                                   target="_blank" class="btn btn-sm btn-info btn-icon" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="products.php?delete=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-danger btn-icon" title="Xóa"
                                   onclick="return confirmDelete('Bạn có chắc muốn xóa sản phẩm này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="fas fa-box-open" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                            <p style="color: #999;">Không tìm thấy sản phẩm nào</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>"
               class="<?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function exportExcel() {
    // Build export URL with current filters
    const urlParams = new URLSearchParams(window.location.search);
    const search = urlParams.get('search') || '';
    const category = urlParams.get('category') || '';
    
    let exportUrl = 'export_products.php?';
    if (search) exportUrl += 'search=' + encodeURIComponent(search) + '&';
    if (category) exportUrl += 'category=' + encodeURIComponent(category);
    
    // Show loading
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xuất...';
    btn.disabled = true;
    
    // Trigger download
    window.location.href = exportUrl;
    
    // Reset button after delay
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 2000);
}

function printTable() {
    const printContent = document.querySelector('.data-table').outerHTML;
    const printWindow = window.open('', '', 'width=900,height=600');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Danh sách sản phẩm - DNQDH Shop</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1 { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background: #f5f5f5; font-weight: bold; }
                img { display: none; }
                .btn, button, a { display: none !important; }
                @media print {
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
            <h1>Danh sách sản phẩm - DNQDH Shop</h1>
            <p>Ngày xuất: ${new Date().toLocaleDateString('vi-VN')} ${new Date().toLocaleTimeString('vi-VN')}</p>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

function confirmDelete(message) {
    return confirm(message);
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>

