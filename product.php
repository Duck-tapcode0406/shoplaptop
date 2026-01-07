<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';

// Lấy các tham số filter
$search = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$monitor = isset($_GET['monitor']) ? trim($_GET['monitor']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' && $_GET['max_price'] !== '999999999' ? (float)$_GET['max_price'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Tạo câu lệnh SQL đếm sản phẩm
$count_sql = "
    SELECT COUNT(*) AS total_products
    FROM 
        product p
    LEFT JOIN 
        price pr 
    ON 
        p.id = pr.product_id
    LEFT JOIN 
        image img 
    ON 
        p.id = img.product_id AND img.sort_order = 1
    WHERE 1=1";
        
$params = [];
$types = '';

// Điều kiện tìm kiếm
if (!empty($search)) {
    $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $types .= "ss";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Điều kiện category (tìm trong tên)
if (!empty($category)) {
    $count_sql .= " AND p.name LIKE ?";
    $types .= "s";
    $params[] = "%" . $category . "%";
}

// Điều kiện brand (tìm trong tên)
if (!empty($brand)) {
    $count_sql .= " AND p.name LIKE ?";
    $types .= "s";
    $params[] = "%" . $brand . "%";
}

// Điều kiện monitor (tìm trong tên)
if (!empty($monitor)) {
    $count_sql .= " AND p.name LIKE ?";
    $types .= "s";
    $params[] = "%" . $monitor . "%";
}

// Điều kiện giá
if ($min_price !== null && $max_price !== null) {
    $count_sql .= " AND pr.price BETWEEN ? AND ?";
    $types .= "dd";
    $params[] = $min_price;
    $params[] = $max_price;
} elseif ($min_price !== null) {
    $count_sql .= " AND pr.price >= ?";
    $types .= "d";
    $params[] = $min_price;
} elseif ($max_price !== null) {
    $count_sql .= " AND pr.price <= ?";
    $types .= "d";
    $params[] = $max_price;
}

$stmt = $conn->prepare($count_sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$count_result = $stmt->get_result();
$total_products = $count_result->fetch_assoc()['total_products'];

// Xác định số sản phẩm mỗi trang và tổng số trang
$products_per_page = 12;
$total_pages = ceil($total_products / $products_per_page);

// Xác định trang hiện tại
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page > 0) ? $page : 1;

// Tính chỉ mục bắt đầu
$offset = ($page - 1) * $products_per_page;

// Truy vấn danh sách sản phẩm
$sql = "
    SELECT 
        p.id,
        p.name,
        p.description,
        pr.price,
        pr.temporary_price,
        pr.discount_start,
        pr.discount_end,
        img.path
    FROM 
        product p
    LEFT JOIN 
        price pr 
    ON 
        p.id = pr.product_id
    LEFT JOIN 
        image img 
    ON 
        p.id = img.product_id AND img.sort_order = 1
    WHERE 1=1";
        
$params = [];
$types = '';

// Điều kiện tìm kiếm
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $types .= "ss";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Điều kiện category
if (!empty($category)) {
    $sql .= " AND p.name LIKE ?";
    $types .= "s";
    $params[] = "%" . $category . "%";
}

// Điều kiện brand
if (!empty($brand)) {
    $sql .= " AND p.name LIKE ?";
    $types .= "s";
    $params[] = "%" . $brand . "%";
}

// Điều kiện monitor
if (!empty($monitor)) {
    $sql .= " AND p.name LIKE ?";
    $types .= "s";
    $params[] = "%" . $monitor . "%";
}

// Điều kiện giá
if ($min_price !== null && $max_price !== null) {
    $sql .= " AND pr.price BETWEEN ? AND ?";
    $types .= "dd";
    $params[] = $min_price;
    $params[] = $max_price;
} elseif ($min_price !== null) {
    $sql .= " AND pr.price >= ?";
    $types .= "d";
    $params[] = $min_price;
} elseif ($max_price !== null) {
    $sql .= " AND pr.price <= ?";
    $types .= "d";
    $params[] = $max_price;
}

// Sắp xếp
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY pr.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY pr.price DESC";
        break;
    case 'name_az':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_za':
        $sql .= " ORDER BY p.name DESC";
        break;
    default:
        $sql .= " ORDER BY p.id DESC";
}

$sql .= " LIMIT ?, ?";

// Thêm offset và limit vào params
$types .= "ii";
$params[] = $offset;
$params[] = $products_per_page;

$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Xác định tiêu đề trang
$page_title = "Tất Cả Sản Phẩm";
if (!empty($category)) {
    $page_title = ucfirst(str_replace('-', ' ', $category));
} elseif (!empty($brand)) {
    $page_title = "Sản phẩm " . strtoupper($brand);
} elseif (!empty($search)) {
    $page_title = "Kết quả tìm kiếm: \"" . htmlspecialchars($search) . "\"";
}
?>

<?php include('includes/header.php'); ?>
    <style>
        /* Hero Banner */
        .hero {
            background: url('images/anhbia.png') center center / cover no-repeat;
            color: white;
            padding: var(--space-5xl) var(--space-md);
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 300px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(108, 92, 231, 0.75) 0%, rgba(162, 155, 254, 0.6) 100%);
            z-index: 0;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: var(--fs-display);
            font-weight: var(--fw-black);
            margin-bottom: var(--space-lg);
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: var(--fs-large);
            margin-bottom: var(--space-2xl);
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: var(--space-3xl);
            margin-top: var(--space-2xl);
        }

        .hero-stat {
            text-align: center;
        }

        .hero-stat-number {
            font-size: var(--fs-h1);
            font-weight: var(--fw-black);
            display: block;
        }

        .hero-stat-label {
            font-size: var(--fs-small);
            opacity: 0.8;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: var(--space-lg);
            margin: calc(-1 * var(--space-3xl)) auto var(--space-3xl);
            max-width: 1200px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 10;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: var(--space-md);
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }

        .filter-group label {
            font-size: var(--fs-small);
            font-weight: var(--fw-bold);
            color: var(--text-secondary);
        }

        .filter-group input,
        .filter-group select {
            padding: var(--space-sm) var(--space-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            transition: border-color var(--transition-fast);
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Products Section */
        .products-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--space-md) var(--space-5xl);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-2xl);
        }

        .section-title {
            font-size: var(--fs-h2);
            font-weight: var(--fw-black);
            color: var(--text-primary);
        }

        .products-count {
            color: var(--text-secondary);
            font-size: var(--fs-body);
        }

        .sort-select {
            padding: var(--space-sm) var(--space-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            cursor: pointer;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-lg);
        }

        /* Product Card */
        .product-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            display: flex;
            flex-direction: column;
            animation: cardFadeIn 0.6s ease-out backwards;
        }

        .product-card:nth-child(1) { animation-delay: 0.05s; }
        .product-card:nth-child(2) { animation-delay: 0.1s; }
        .product-card:nth-child(3) { animation-delay: 0.15s; }
        .product-card:nth-child(4) { animation-delay: 0.2s; }
        .product-card:nth-child(5) { animation-delay: 0.25s; }
        .product-card:nth-child(6) { animation-delay: 0.3s; }
        .product-card:nth-child(7) { animation-delay: 0.35s; }
        .product-card:nth-child(8) { animation-delay: 0.4s; }
        .product-card:nth-child(9) { animation-delay: 0.45s; }
        .product-card:nth-child(10) { animation-delay: 0.5s; }
        .product-card:nth-child(11) { animation-delay: 0.55s; }
        .product-card:nth-child(12) { animation-delay: 0.6s; }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 40px rgba(108, 92, 231, 0.2);
        }

        .product-card-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-image-wrapper {
            position: relative;
            padding-top: 100%;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            overflow: hidden;
        }

        .product-image-wrapper img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-normal);
        }

        .product-card:hover .product-image-wrapper img {
            transform: scale(1.08);
        }

        /* Shine effect on image */
        .product-image-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s ease;
            z-index: 2;
        }

        .product-card:hover .product-image-wrapper::before {
            left: 100%;
        }

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 3;
            animation: badgePulse 2s infinite;
        }

        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .product-badge.sale {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4);
        }

        .product-badge.new {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.4);
        }

        .product-wishlist {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #adb5bd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 3;
            opacity: 0;
            transform: translateY(-10px);
        }

        .product-card:hover .product-wishlist {
            opacity: 1;
            transform: translateY(0);
        }

        .product-wishlist:hover {
            color: #e74c3c;
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);
        }

        .product-wishlist.active {
            color: #e74c3c;
            background: #fff5f5;
        }

        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .product-name {
            font-size: 15px;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 10px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
            min-height: 42px;
            transition: color 0.3s ease;
        }

        .product-card:hover .product-name {
            color: #6C5CE7;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .product-rating .stars {
            color: #ffc107;
            font-size: 13px;
            display: flex;
            gap: 2px;
        }

        .product-rating .stars i {
            transition: transform 0.2s ease;
        }

        .product-card:hover .product-rating .stars i {
            animation: starTwinkle 0.5s ease forwards;
        }

        .product-card:hover .product-rating .stars i:nth-child(1) { animation-delay: 0.05s; }
        .product-card:hover .product-rating .stars i:nth-child(2) { animation-delay: 0.1s; }
        .product-card:hover .product-rating .stars i:nth-child(3) { animation-delay: 0.15s; }
        .product-card:hover .product-rating .stars i:nth-child(4) { animation-delay: 0.2s; }
        .product-card:hover .product-rating .stars i:nth-child(5) { animation-delay: 0.25s; }

        @keyframes starTwinkle {
            0% { transform: scale(1); }
            50% { transform: scale(1.3) rotate(15deg); }
            100% { transform: scale(1) rotate(0); }
        }

        .product-rating .count {
            font-size: 12px;
            color: #868e96;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .product-price-current {
            font-size: 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #6C5CE7, #a29bfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .product-price-original {
            font-size: 13px;
            color: #adb5bd;
            text-decoration: line-through;
        }

        /* Hover Actions */
        .product-actions-hover {
            padding: 0 20px 20px;
            background: white;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .product-card:hover .product-actions-hover {
            opacity: 1;
            transform: translateY(0);
        }

        .product-actions-hover .btn {
            width: 100%;
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 10px;
            background: linear-gradient(135deg, #6C5CE7 0%, #a29bfe 100%);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .product-actions-hover .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .product-actions-hover .btn:hover::before {
            left: 100%;
        }

        .product-actions-hover .btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.4);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: var(--space-sm);
            margin-top: var(--space-3xl);
        }

        .pagination a,
        .pagination span {
            padding: var(--space-sm) var(--space-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--text-primary);
            font-weight: var(--fw-medium);
            transition: all var(--transition-fast);
        }

        .pagination a:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .pagination .active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: var(--space-5xl) var(--space-md);
        }

        .empty-state-icon {
            font-size: 80px;
            color: var(--text-light);
            margin-bottom: var(--space-lg);
        }

        .empty-state-text {
            color: var(--text-secondary);
            margin-bottom: var(--space-2xl);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .filter-form {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            .filter-form {
                grid-template-columns: 1fr;
            }
            .hero h1 {
                font-size: var(--fs-h1);
            }
            .hero-stats {
                flex-direction: column;
                gap: var(--space-lg);
            }
            .product-wishlist {
                opacity: 1;
                transform: translateY(0);
            }
            .product-actions-hover {
                opacity: 1;
                transform: translateY(0);
            }
            .product-info {
                padding: 15px;
            }
            .product-name {
                font-size: 14px;
                min-height: 38px;
            }
            .product-price-current {
                font-size: 16px;
            }
        }
    </style>

    <!-- Hero Banner -->
    <section class="hero">
        <div class="hero-content">
            <h1><?php echo $page_title; ?></h1>
            <p>Khám phá bộ sưu tập sản phẩm công nghệ chất lượng cao với giá tốt nhất</p>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-number"><?php echo $total_products; ?></span>
                    <span class="hero-stat-label">Sản phẩm</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-number">100%</span>
                    <span class="hero-stat-label">Chính hãng</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-number">24/7</span>
                    <span class="hero-stat-label">Hỗ trợ</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <form method="GET" action="product.php" class="filter-form">
            <?php if (!empty($category)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            <?php endif; ?>
            <?php if (!empty($brand)): ?>
                <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand); ?>">
            <?php endif; ?>
            
            <div class="filter-group">
                <label for="search"><i class="fas fa-search"></i> Tìm kiếm</label>
                <input type="text" id="search" name="search" placeholder="Nhập tên sản phẩm..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="filter-group">
                <label for="min_price"><i class="fas fa-tag"></i> Giá từ</label>
                <input type="number" id="min_price" name="min_price" placeholder="0" 
                       value="<?php echo $min_price !== null ? $min_price : ''; ?>">
            </div>
            
            <div class="filter-group">
                <label for="max_price"><i class="fas fa-tag"></i> Giá đến</label>
                <input type="number" id="max_price" name="max_price" placeholder="999,999,999" 
                       value="<?php echo $max_price !== null ? $max_price : ''; ?>">
            </div>
            
            <div class="filter-group">
                <label for="sort"><i class="fas fa-sort"></i> Sắp xếp</label>
                <select id="sort" name="sort">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Giá thấp → cao</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Giá cao → thấp</option>
                    <option value="name_az" <?php echo $sort === 'name_az' ? 'selected' : ''; ?>>Tên A → Z</option>
                    <option value="name_za" <?php echo $sort === 'name_za' ? 'selected' : ''; ?>>Tên Z → A</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
        </form>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="section-header">
            <h2 class="section-title">
                <?php if (!empty($category) || !empty($brand) || !empty($search)): ?>
                    <?php echo $page_title; ?>
                <?php else: ?>
                    Tất Cả Sản Phẩm
                <?php endif; ?>
            </h2>
            <span class="products-count"><?php echo $total_products; ?> sản phẩm</span>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="product-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Xử lý giá và giảm giá
                    $current_time = date('Y-m-d H:i:s');
                    $price = $row['price'] ?? 0;
                    $original_price = $price;
                    $is_on_sale = false;
                    $discount_percent = 0;

                    if (!empty($row['temporary_price']) && !empty($row['discount_start']) && !empty($row['discount_end'])) {
                        if ($current_time >= $row['discount_start'] && $current_time <= $row['discount_end']) {
                            $is_on_sale = true;
                            $price = $row['temporary_price'];
                            if ($original_price > 0) {
                                $discount_percent = round((($original_price - $price) / $original_price) * 100);
                            }
                        }
                    }

                    // Xử lý đường dẫn ảnh
                    $rawPath = isset($row['path']) ? trim($row['path']) : '';
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
                    <div class="product-card">
                        <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="product-card-link">
                            <div class="product-image-wrapper">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                     alt="<?php echo htmlspecialchars($row['name']); ?>"
                                     onerror="this.onerror=null; this.src='images/no-image.png';">
                                
                                <!-- Sale Badge -->
                                <?php if ($is_on_sale): ?>
                                    <span class="product-badge sale">-<?php echo $discount_percent; ?>%</span>
                                <?php else: ?>
                                    <span class="product-badge new">Mới</span>
                                <?php endif; ?>

                                <!-- Wishlist Button -->
                                <button type="button" class="product-wishlist" data-product-id="<?php echo $row['id']; ?>" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(this, <?php echo $row['id']; ?>)">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>

                            <!-- Product Info -->
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h3>

                                <!-- Rating -->
                                <div class="product-rating">
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="count">(120 reviews)</span>
                                </div>

                                <!-- Price -->
                                <div class="product-price">
                                    <?php if ($is_on_sale): ?>
                                        <span class="product-price-original"><?php echo number_format($original_price, 0, ',', '.'); ?> ₫</span>
                                        <span class="product-price-current"><?php echo number_format($price, 0, ',', '.'); ?> ₫</span>
                                    <?php else: ?>
                                        <span class="product-price-current"><?php echo number_format($price, 0, ',', '.'); ?> ₫</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Actions - Hover -->
                        <div class="product-actions-hover">
                            <form method="POST" action="add_to_cart.php" style="display: flex; gap: 10px; width: 100%;" onclick="event.stopPropagation();">
                                <?php echo getCSRFTokenField(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
                                    <i class="fas fa-shopping-cart"></i> Thêm Vào Giỏ Hàng
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    // Build query string for pagination
                    $query_params = [];
                    if (!empty($search)) $query_params['search'] = $search;
                    if (!empty($category)) $query_params['category'] = $category;
                    if (!empty($brand)) $query_params['brand'] = $brand;
                    if ($min_price !== null) $query_params['min_price'] = $min_price;
                    if ($max_price !== null) $query_params['max_price'] = $max_price;
                    if ($sort !== 'newest') $query_params['sort'] = $sort;
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => 1])); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page - 1])); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $page + 1])); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($query_params, ['page' => $total_pages])); ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="empty-state-text">Không tìm thấy sản phẩm</h3>
                <p class="empty-state-text">Thử lại với từ khóa hoặc lọc khác</p>
                <a href="product.php" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Xem Tất Cả Sản Phẩm
                </a>
            </div>
        <?php endif; ?>
    </section>

    <?php include('includes/footer.php'); ?>

    <script>
        function toggleWishlist(btn, productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào danh sách yêu thích.');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            const icon = btn.querySelector('i');
            const isInWishlist = icon.classList.contains('fas');
            const url = isInWishlist ? 'remove_wishlist.php' : 'add_wishlist.php';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                    btn.classList.toggle('active');
                    if (typeof showSuccess === 'function') {
                        showSuccess('Thành công', data.message);
                    }
                } else {
                    if (typeof showError === 'function') {
                        showError('Lỗi', data.message || 'Có lỗi xảy ra');
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thực hiện thao tác');
            });
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
