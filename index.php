<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';

// Lưu thời gian hiện tại vào session (nếu chưa có)
if (!isset($_SESSION['current_time'])) {
    $_SESSION['current_time'] = date('Y-m-d H:i:s');  // Lưu thời gian hiện tại
}

// Lấy giá trị tìm kiếm từ form - chỉ lấy khi có giá trị thực sự
$search = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' && $_GET['max_price'] !== '999999999' ? (float)$_GET['max_price'] : null;

// Tạo câu lệnh SQL đếm sản phẩm - chỉ áp dụng filter khi có giá trị
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

// Chỉ thêm điều kiện tìm kiếm nếu có từ khóa
if (!empty($search)) {
    $count_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $types .= "ss";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Chỉ thêm điều kiện giá nếu có filter giá
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
$products_per_page = 8;
$total_pages = ceil($total_products / $products_per_page);

// Xác định trang hiện tại (mặc định là trang 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page > 0) ? $page : 1;  // Đảm bảo trang không âm

// Tính chỉ mục bắt đầu cho truy vấn SQL
$offset = ($page - 1) * $products_per_page;

// Truy vấn danh sách sản phẩm - chỉ áp dụng filter khi có giá trị
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

// Chỉ thêm điều kiện tìm kiếm nếu có từ khóa
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $types .= "ss";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Chỉ thêm điều kiện giá nếu có filter giá
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

$sql .= " ORDER BY p.id DESC LIMIT ?, ?";  // Phân trang

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
?>

<?php include('includes/header.php'); ?>
    <style>
        /* Hero Banner */
        .hero {
            background: url('images/anhbia.png') center center / cover no-repeat;
            color: white;
            padding: var(--space-5xl) var(--space-md);
            text-align: center;
            margin-bottom: var(--space-5xl);
            border-radius: var(--radius-lg);
            position: relative;
            overflow: hidden;
            animation: fadeInDown 0.8s ease-out;
            min-height: 350px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(108, 92, 231, 0.7) 0%, rgba(162, 155, 254, 0.5) 100%);
            z-index: 0;
        }

        .hero > * {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: var(--fs-display);
            margin-bottom: var(--space-lg);
            color: white;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: var(--fs-body-lg);
            margin-bottom: var(--space-2xl);
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }

        /* Filters Section */
        .filters-container {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-3xl);
            animation: fadeInUp 0.6s ease-out 0.2s both;
            transition: box-shadow var(--transition-normal);
        }

        .filters-container:hover {
            box-shadow: var(--shadow-md);
        }

        .filter-group {
            margin-bottom: var(--space-lg);
        }

        .filter-group label {
            font-weight: var(--fw-bold);
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
            display: block;
        }

        .filter-group input {
            width: 100%;
            padding: var(--space-sm) var(--space-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
        }

        /* Products Header -->
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-2xl);
            flex-wrap: wrap;
            gap: var(--space-md);
        }

        .results-count {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .sort-select {
            padding: var(--space-sm) var(--space-md);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: var(--space-5xl) var(--space-md);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--text-light);
            margin-bottom: var(--space-lg);
        }

        .empty-state-text {
            color: var(--text-secondary);
            margin-bottom: var(--space-lg);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--space-md);
            margin-top: var(--space-3xl);
            padding-top: var(--space-3xl);
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        /* Product Card Styles - Modern Design */
        .product-card {
            position: relative;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: fadeInUp 0.6s ease-out backwards;
        }

        .product-card:nth-child(1) { animation-delay: 0.05s; }
        .product-card:nth-child(2) { animation-delay: 0.1s; }
        .product-card:nth-child(3) { animation-delay: 0.15s; }
        .product-card:nth-child(4) { animation-delay: 0.2s; }
        .product-card:nth-child(5) { animation-delay: 0.25s; }
        .product-card:nth-child(6) { animation-delay: 0.3s; }
        .product-card:nth-child(7) { animation-delay: 0.35s; }
        .product-card:nth-child(8) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
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

        .product-clickable-area {
            cursor: pointer;
            display: block;
        }

        /* Product Image */
        .product-image-wrapper {
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%);
        }

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

        .product-image {
            width: 100%;
            height: 240px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.08);
        }

        /* Product Badge */
        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 3;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
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

        /* Wishlist Button */
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
            z-index: 3;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

        .product-wishlist.active i {
            animation: heartBeat 0.6s ease;
        }

        @keyframes heartBeat {
            0% { transform: scale(1); }
            25% { transform: scale(1.3); }
            50% { transform: scale(1); }
            75% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }

        /* Product Info */
        .product-info {
            padding: 20px;
            background: white;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 10px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            transition: color 0.3s ease;
            min-height: 44px;
        }

        .product-card:hover .product-name {
            color: #6C5CE7;
        }

        /* Product Rating */
        .product-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .product-rating .stars {
            display: flex;
            gap: 2px;
        }

        .product-rating .stars i {
            font-size: 13px;
            color: #ffc107;
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

        /* Product Price */
        .product-price {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .product-price-current {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #6C5CE7, #a29bfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .product-price-original {
            font-size: 14px;
            color: #adb5bd;
            text-decoration: line-through;
        }

        /* Product Actions on Hover */
        .product-actions-hover {
            padding: 0 20px 20px;
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
            justify-content: center;
            padding: 14px 20px;
            font-weight: 600;
            border-radius: 12px;
            background: linear-gradient(135deg, #6C5CE7 0%, #a29bfe 100%);
            border: none;
            color: white;
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

        .product-actions-hover .btn i {
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .product-actions-hover .btn:hover i {
            transform: translateX(-3px);
            animation: cartBounce 0.5s ease;
        }

        @keyframes cartBounce {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(3px); }
        }

        /* Quick View Overlay */
        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(108, 92, 231, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 4;
            pointer-events: none;
        }

        .product-card:hover .product-overlay {
            opacity: 0;
        }

        /* Grid improvements */
        .grid-4 {
            gap: 24px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-image {
                height: 180px;
            }
            
            .product-wishlist {
                opacity: 1;
                transform: translateY(0);
            }
            
            .product-actions-hover {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
        <!-- Hero Banner -->
    <div class="container">
        <div class="hero">
            <h1>Khám Phá Sản Phẩm Chất Lượng</h1>
            <p>Hàng triệu sản phẩm công nghệ với giá tốt nhất - Giao hàng nhanh chóng</p>
            <?php if (isset($_SESSION['username'])): ?>
                <p style="font-size: var(--fs-small); color: rgba(255,255,255,0.8);">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
            <?php endif; ?>
        </div>

        <!-- Filters Section -->
        <div class="filters-container">
            <h3 style="margin-bottom: var(--space-lg);">
                <i class="fas fa-filter" style="margin-right: var(--space-sm);"></i>Tìm Kiếm & Lọc
            </h3>

            <!-- BEGIN: unified search & filter -->
            <form method="GET" action="" class="filters-grid-form">
                <div class="grid grid-3" style="align-items:end;">
                    <div style="grid-column: span 2;">
                        <div class="filter-group">
                            <label for="search"><i class="fas fa-search" style="margin-right: 8px;"></i>Tìm kiếm sản phẩm</label>
                            <input type="text" name="search" id="search" placeholder="Nhập tên sản phẩm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary" style="height:44px;min-width:120px;">
                            <i class="fas fa-sliders-h"></i> Lọc
                        </button>
                    </div>
                </div>

                <div class="grid grid-3" style="margin-top:12px;align-items:end;">
                    <div>
                        <div class="filter-group">
                            <label for="min_price"><i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Giá tối thiểu</label>
                            <select name="min_price" id="min_price" class="filter-group input" style="width: 100%; padding: var(--space-sm) var(--space-md); border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: var(--fs-body);">
                                <option value="">Chọn giá tối thiểu</option>
                                <option value="0" <?php echo (isset($_GET['min_price']) && $_GET['min_price'] == '0') ? 'selected' : ''; ?>>0</option>
                                <option value="5000000" <?php echo (isset($_GET['min_price']) && $_GET['min_price'] == '5000000') ? 'selected' : ''; ?>>5 triệu</option>
                                <option value="10000000" <?php echo (isset($_GET['min_price']) && $_GET['min_price'] == '10000000') ? 'selected' : ''; ?>>10 triệu</option>
                                <option value="15000000" <?php echo (isset($_GET['min_price']) && $_GET['min_price'] == '15000000') ? 'selected' : ''; ?>>15 triệu</option>
                                <option value="20000000" <?php echo (isset($_GET['min_price']) && $_GET['min_price'] == '20000000') ? 'selected' : ''; ?>>20 triệu</option>
                                <option value="50000000" <?php echo (isset($_GET['min_price']) && $_GET['min_price'] == '50000000') ? 'selected' : ''; ?>>50 triệu</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="filter-group">
                            <label for="max_price"><i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Giá tối đa</label>
                            <select name="max_price" id="max_price" class="filter-group input" style="width: 100%; padding: var(--space-sm) var(--space-md); border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: var(--fs-body);">
                                <option value="">Chọn giá tối đa</option>
                                <option value="5000000" <?php echo (isset($_GET['max_price']) && $_GET['max_price'] == '5000000') ? 'selected' : ''; ?>>5 triệu</option>
                                <option value="10000000" <?php echo (isset($_GET['max_price']) && $_GET['max_price'] == '10000000') ? 'selected' : ''; ?>>10 triệu</option>
                                <option value="15000000" <?php echo (isset($_GET['max_price']) && $_GET['max_price'] == '15000000') ? 'selected' : ''; ?>>15 triệu</option>
                                <option value="30000000" <?php echo (isset($_GET['max_price']) && $_GET['max_price'] == '30000000') ? 'selected' : ''; ?>>30 triệu</option>
                                <option value="50000000" <?php echo (isset($_GET['max_price']) && $_GET['max_price'] == '50000000') ? 'selected' : ''; ?>>50 triệu</option>
                                <option value="100000000" <?php echo (isset($_GET['max_price']) && $_GET['max_price'] == '100000000') ? 'selected' : ''; ?>>100 triệu</option>
                                <option value="999999999" <?php echo (isset($_GET['max_price']) && $_GET['max_price'] == '999999999') ? 'selected' : ''; ?>>Không giới hạn</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <a href="index.php" class="btn btn-secondary" style="height:44px;min-width:120px;display:flex;align-items:center;justify-content:center;text-decoration:none;">× Xóa Lọc</a>
                    </div>
                </div>
            </form>
            <!-- END: unified search & filter -->
        </div>

        <!-- Products Section -->
        <div class="products-header">
            <h2 style="margin: 0;">
                <i class="fas fa-box" style="margin-right: var(--space-md);"></i>Sản Phẩm Khả Dụng
            </h2>
            <div class="flex" style="gap: var(--space-md);">
                <span class="results-count">
                    Hiển thị <strong><?php echo ($result && $result->num_rows > 0) ? $result->num_rows : 0; ?></strong> sản phẩm
                </span>
                <select class="sort-select">
                    <option>Sắp xếp theo...</option>
                    <option value="latest">Mới nhất</option>
                    <option value="price-low">Giá: Thấp → Cao</option>
                    <option value="price-high">Giá: Cao → Thấp</option>
                    <option value="popular">Phổ biến nhất</option>
                </select>
            </div>
        </div>

        <!-- Products Grid -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="grid grid-4" style="margin-bottom: var(--space-5xl);">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Sử dụng thời gian hiện tại thực tế thay vì session time
                    $current_time = date('Y-m-d H:i:s');
                    $discount_start = $row['discount_start'];
                    $discount_end = $row['discount_end'];

                    // Kiểm tra khuyến mãi với xử lý NULL an toàn
                    $is_on_sale = false;
                    $price = !empty($row['price']) ? $row['price'] : 0;
                    $original_price = null;
                    $discount_percent = 0;
                    
                    // Chỉ kiểm tra khuyến mãi nếu có đầy đủ thông tin
                    if (!empty($row['discount_start']) && !empty($row['discount_end']) && 
                        !empty($row['temporary_price']) && !empty($row['price'])) {
                        if ($current_time >= $row['discount_start'] && $current_time <= $row['discount_end']) {
                            $price = $row['temporary_price'];
                            $original_price = $row['price'];
                            $is_on_sale = true;
                            if ($original_price > 0) {
                                $discount_percent = round((($original_price - $price) / $original_price) * 100);
                            }
                        }
                    }
                    ?>
                    <div class="product-card">
                        <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="product-clickable-area" style="text-decoration: none; color: inherit;">
                            <!-- Product Image -->
                            <div class="product-image-wrapper">
                                <?php
                                $imagePath = !empty($row['path']) ? 'admin/' . htmlspecialchars($row['path']) : 'images/no-image.png';
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image" onerror="this.onerror=null; this.src='images/no-image.png';">
                                
                                <!-- Sale Badge -->
                                <?php if ($is_on_sale): ?>
                                    <span class="product-badge sale">-<?php echo $discount_percent; ?>%</span>
                                <?php else: ?>
                                    <span class="product-badge new">Mới</span>
                                <?php endif; ?>

                                <!-- Wishlist Button -->
                                <button type="button" class="product-wishlist" data-product-id="<?php echo $row['id']; ?>" onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(this, <?php echo $row['id']; ?>)" title="Thêm vào yêu thích">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>

                            <!-- Product Info -->
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h3>

                                <!-- Rating -->
                                <div class="product-rating">
                                    <div class="stars">
                                        <?php 
                                        $rating = rand(35, 50) / 10; // Random rating 3.5-5.0
                                        $fullStars = floor($rating);
                                        $hasHalf = ($rating - $fullStars) >= 0.5;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $fullStars) {
                                                echo '<i class="fas fa-star"></i>';
                                            } elseif ($i == $fullStars + 1 && $hasHalf) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="count">(<?php echo rand(50, 500); ?>)</span>
                                </div>

                                <!-- Price -->
                                <div class="product-price">
                                    <?php if ($is_on_sale): ?>
                                        <span class="product-price-current"><?php echo number_format($price, 0, ',', '.'); ?> ₫</span>
                                        <span class="product-price-original"><?php echo number_format($original_price, 0, ',', '.'); ?> ₫</span>
                                    <?php else: ?>
                                        <span class="product-price-current"><?php echo number_format($price, 0, ',', '.'); ?> ₫</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Actions - Hover -->
                        <div class="product-actions-hover">
                            <form method="POST" action="add_to_cart.php" onclick="event.stopPropagation();">
                                <?php echo getCSRFTokenField(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart"></i> Thêm Vào Giỏ Hàng
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <span class="pagination-info">Trang <?php echo $page; ?>/<?php echo $total_pages; ?></span>
                
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $min_price !== null ? '&min_price=' . $min_price : ''; ?><?php echo $max_price !== null ? '&max_price=' . $max_price : ''; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-chevron-left"></i><i class="fas fa-chevron-left"></i>
                    </a>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $min_price !== null ? '&min_price=' . $min_price : ''; ?><?php echo $max_price !== null ? '&max_price=' . $max_price : ''; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $min_price !== null ? '&min_price=' . $min_price : ''; ?><?php echo $max_price !== null ? '&max_price=' . $max_price : ''; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $min_price !== null ? '&min_price=' . $min_price : ''; ?><?php echo $max_price !== null ? '&max_price=' . $max_price : ''; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-chevron-right"></i><i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="empty-state-text">Không tìm thấy sản phẩm</h3>
                <p class="empty-state-text">Thử lại với từ khóa hoặc lọc khác</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Xem Tất Cả Sản Phẩm
                </a>
            </div>
        <?php endif; ?>
    </div>

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

            // Determine action: add or remove
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
                    // Toggle UI
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

        // Sort functionality
        document.querySelector('.sort-select')?.addEventListener('change', (e) => {
            const sortValue = e.target.value;
            // Add your sorting logic here
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
