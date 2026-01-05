<?php
// Load required files
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/validator.php';

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$search_query = isset($_GET['search']) ? Validator::sanitize($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? Validator::sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Validate sort_by
$allowed_sorts = ['newest', 'price_low', 'price_high', 'popular'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'newest';
}

// Build query with prepared statements
$where_conditions = [];
$params = [];
$types = '';

// Base condition
$where_conditions[] = "p.is_active = 1";

// Category filter
if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

// Search filter
if (!empty($search_query)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$where = "WHERE " . implode(" AND ", $where_conditions);

// Order by
$order = "ORDER BY p.id DESC";
if ($sort_by === 'price_low') {
    $order = "ORDER BY p.price ASC";
} elseif ($sort_by === 'price_high') {
    $order = "ORDER BY p.price DESC";
} elseif ($sort_by === 'popular') {
    $order = "ORDER BY p.id DESC";
}

// Count total products using prepared statement
$count_query = "SELECT COUNT(*) as total FROM product p $where";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Fetch products using prepared statement
$query = "SELECT p.id, p.name, p.price, p.rating, img.path
          FROM product p 
          LEFT JOIN image img ON p.id = img.product_id AND img.sort_order = 1
          $where 
          $order 
          LIMIT ?, ?";
$params[] = $offset;
$params[] = $per_page;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Mục Sản Phẩm - ModernShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .product-browse-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .browse-header {
            margin-bottom: var(--space-3xl);
        }

        .browse-header h1 {
            font-size: var(--fs-h1);
            margin-bottom: var(--space-md);
        }

        .browse-header p {
            color: var(--text-secondary);
            font-size: var(--fs-large);
        }

        .browse-toolbar {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: var(--space-lg);
            margin-bottom: var(--space-3xl);
            padding: var(--space-lg);
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .search-box {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0 var(--space-md);
            background: var(--bg-primary);
        }

        .search-box input {
            flex: 1;
            border: none;
            background: transparent;
            padding: var(--space-sm) 0;
            font-size: var(--fs-body);
        }

        .search-box button {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 0 var(--space-sm);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-xs);
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .filter-group select {
            border: 1px solid var(--border-color);
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            background: white;
            cursor: pointer;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-5xl);
        }

        .product-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .product-image {
            width: 100%;
            height: 250px;
            background-color: var(--bg-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-badge {
            position: absolute;
            top: var(--space-md);
            right: var(--space-md);
            background-color: var(--danger);
            color: white;
            padding: var(--space-xs) var(--space-md);
            border-radius: var(--radius-full);
            font-size: var(--fs-small);
            font-weight: var(--fw-bold);
        }

        .product-info {
            padding: var(--space-lg);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-weight: var(--fw-bold);
            font-size: var(--fs-h5);
            margin-bottom: var(--space-xs);
            color: var(--text-primary);
            flex: 1;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            margin-bottom: var(--space-md);
            font-size: var(--fs-small);
        }

        .stars {
            color: #FFB800;
        }

        .product-price {
            font-size: var(--fs-h4);
            font-weight: var(--fw-bold);
            color: var(--primary);
            margin-bottom: var(--space-md);
        }

        .product-actions {
            display: flex;
            gap: var(--space-sm);
        }

        .product-actions .btn {
            flex: 1;
            padding: var(--space-sm) var(--space-md);
            font-size: var(--fs-small);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-5xl);
        }

        .pagination a,
        .pagination span {
            padding: var(--space-sm) var(--space-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--text-primary);
        }

        .pagination a:hover,
        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: var(--space-5xl) var(--space-md);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: var(--space-lg);
        }

        @media (max-width: 768px) {
            .browse-toolbar {
                grid-template-columns: 1fr;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="product-browse-container">
        <!-- Page Header -->
        <div class="browse-header">
            <h1>Danh Mục Sản Phẩm</h1>
            <p><?php echo $total; ?> sản phẩm được tìm thấy</p>
        </div>

        <!-- Toolbar -->
        <div class="browse-toolbar">
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Tìm sản phẩm..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" title="Tìm kiếm">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <div class="filter-group">
                <label>Sắp xếp theo</label>
                <form method="GET" style="display: flex;">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Giá: Thấp đến Cao</option>
                        <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Giá: Cao đến Thấp</option>
                        <option value="popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>Phổ biến</option>
                    </select>
                </form>
            </div>

            <div style="display: flex; align-items: center; gap: var(--space-md);">
                <span style="font-size: var(--fs-small); color: var(--text-secondary);">
                    Hiển thị: <strong><?php echo $per_page; ?></strong> sản phẩm/trang
                </span>
            </div>
        </div>

        <!-- Product Grid -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="product-grid">
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php 
                            $rawPath = isset($product['path']) ? trim($product['path']) : '';
                            
                            if (!empty($rawPath)) {
                                if (strpos($rawPath, 'uploads/') === 0) {
                                    $imagePath = 'admin/' . $rawPath;
                                } else {
                                    $imagePath = 'admin/uploads/' . $rawPath;
                                }
                            } else {
                                $imagePath = 'images/no-image.png';
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null; this.src='images/no-image.png';">
                            <div class="product-badge">MỚI</div>
                        </div>

                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>

                            <div class="product-rating">
                                <span class="stars">
                                    <?php
                                    $rating = intval($product['rating'] ?? 4);
                                    for ($i = 0; $i < 5; $i++) {
                                        echo $i < $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </span>
                                <span><?php echo $rating; ?>/5</span>
                            </div>

                            <div class="product-price">
                                <?php echo number_format($product['price'], 0, ',', '.'); ?> ₫
                            </div>

                            <div class="product-actions">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-bag"></i> Thêm
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_id > 0 ? '&id=' . $category_id : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Đầu tiên
                        </a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_id > 0 ? '&id=' . $category_id : ''; ?>">
                            Trước
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_id > 0 ? '&id=' . $category_id : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_id > 0 ? '&id=' . $category_id : ''; ?>">
                            Tiếp
                        </a>
                        <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo $category_id > 0 ? '&id=' . $category_id : ''; ?>">
                            Cuối cùng <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2>Không tìm thấy sản phẩm</h2>
                <p style="color: var(--text-secondary); margin-bottom: var(--space-2xl);">
                    Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm
                </p>
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left"></i> Quay Lại Trang Chủ
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

