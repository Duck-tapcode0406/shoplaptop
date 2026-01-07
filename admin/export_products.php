<?php
/**
 * Export Products to Excel (CSV format compatible with Excel)
 */
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
$admin_data = $admin_result ? $admin_result->fetch_assoc() : null;

if (!$admin_data || $admin_data['is_admin'] != 1) {
    header('Location: ../index.php');
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Build query
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND p.name LIKE '%$search%'";
}
if ($category) {
    $where .= " AND p.category_id = $category";
}

// Get all products for export
$products = $conn->query("
    SELECT 
        p.id,
        p.name,
        p.description,
        s.name as supplier_name,
        u.name as unit_name,
        pr.price,
        pr.temporary_price,
        pr.discount_start,
        pr.discount_end,
        COALESCE(rd.quantity, 0) as stock,
        COALESCE((SELECT SUM(quantity) FROM order_details WHERE product_id = p.id AND status = 'paid'), 0) as sold,
        COALESCE((SELECT AVG(rating) FROM reviews WHERE product_id = p.id), 0) as avg_rating,
        COALESCE((SELECT COUNT(*) FROM reviews WHERE product_id = p.id), 0) as review_count
    FROM product p
    LEFT JOIN price pr ON p.id = pr.product_id
    LEFT JOIN supplier s ON p.supplier_id = s.id
    LEFT JOIN unit u ON p.unit_id = u.id
    LEFT JOIN receipt_details rd ON p.id = rd.product_id
    $where
    GROUP BY p.id
    ORDER BY p.id DESC
");

// Set headers for Excel download
$filename = 'products_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 (Excel compatibility)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header row
fputcsv($output, [
    'ID',
    'Tên sản phẩm',
    'Mô tả',
    'Nhà cung cấp',
    'Đơn vị',
    'Giá gốc (VNĐ)',
    'Giá khuyến mãi (VNĐ)',
    'Bắt đầu KM',
    'Kết thúc KM',
    'Tồn kho',
    'Đã bán',
    'Đánh giá TB',
    'Số đánh giá',
    'Trạng thái'
]);

// Write data rows
if ($products && $products->num_rows > 0) {
    while ($product = $products->fetch_assoc()) {
        $now = date('Y-m-d H:i:s');
        $is_on_sale = !empty($product['discount_start']) && !empty($product['discount_end']) && 
                      $now >= $product['discount_start'] && $now <= $product['discount_end'];
        
        // Determine status
        if ($is_on_sale) {
            $status = 'Đang giảm giá';
        } elseif ($product['stock'] > 0) {
            $status = 'Còn hàng';
        } else {
            $status = 'Hết hàng';
        }
        
        fputcsv($output, [
            $product['id'],
            $product['name'],
            strip_tags($product['description'] ?? ''),
            $product['supplier_name'] ?? '',
            $product['unit_name'] ?? '',
            $product['price'] ?? 0,
            $product['temporary_price'] ?? '',
            $product['discount_start'] ?? '',
            $product['discount_end'] ?? '',
            $product['stock'],
            $product['sold'],
            number_format($product['avg_rating'], 1),
            $product['review_count'],
            $status
        ]);
    }
}

fclose($output);
exit();

