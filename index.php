<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

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
            background: linear-gradient(135deg, #6C5CE7 0%, #A29BFE 100%);
            color: white;
            padding: var(--space-5xl) var(--space-md);
            text-align: center;
            margin-bottom: var(--space-5xl);
            border-radius: var(--radius-lg);
            position: relative;
            overflow: hidden;
            animation: fadeInDown 0.8s ease-out;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: spin 20s linear infinite;
        }

        .hero > * {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: var(--fs-display);
            margin-bottom: var(--space-lg);
            color: white;
        }

        .hero p {
            font-size: var(--fs-body-lg);
            margin-bottom: var(--space-2xl);
            color: rgba(255, 255, 255, 0.9);
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
    </style>
</head>
<body>
        <!-- Hero Banner -->
    <div class="container">
        <div class="hero">
            <h1><i class="fas fa-shopping-bag" style="margin-right: var(--space-md);"></i>ModernShop</h1>
            <p>Khám phá hàng triệu sản phẩm chất lượng cao với giá tốt nhất</p>
            <?php if (isset($_SESSION['username'])): ?>
                <p style="font-size: var(--fs-small); color: rgba(255,255,255,0.8);">Xin chào, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
            <?php endif; ?>
        </div>

        <!-- Filters Section -->
        <div class="filters-container">
            <h3 style="margin-bottom: var(--space-lg);">
                <i class="fas fa-filter" style="margin-right: var(--space-sm);"></i>Tìm Kiếm & Lọc
            </h3>
            
            <div class="grid grid-3">
                <!-- Search by name -->
                <form method="GET" action="" style="grid-column: span 2;">
                    <div class="filter-group">
                        <label for="search">
                            <i class="fas fa-search" style="margin-right: 8px;"></i>Tìm kiếm sản phẩm
                        </label>
                        <input type="text" name="search" id="search" placeholder="Nhập tên sản phẩm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </form>

                <!-- Price Filter -->
                <form method="GET" action="">
                    <button type="submit" class="btn btn-primary btn-block" style="width: 100%; margin-top: 22px;">
                        <i class="fas fa-sliders-h"></i> Lọc
                    </button>
                </form>
            </div>

            <div class="grid grid-3">
                <!-- Min Price -->
                <form method="GET" action="">
                    <div class="filter-group">
                        <label for="min_price">
                            <i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Giá tối thiểu
                        </label>
                        <input type="number" name="min_price" id="min_price" placeholder="0" value="<?php echo isset($_GET['min_price']) && $_GET['min_price'] !== '' ? htmlspecialchars($_GET['min_price']) : ''; ?>" min="0">
                    </div>
                </form>

                <!-- Max Price -->
                <form method="GET" action="">
                    <div class="filter-group">
                        <label for="max_price">
                            <i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Giá tối đa
                        </label>
                        <input type="number" name="max_price" id="max_price" placeholder="Không giới hạn" value="<?php echo isset($_GET['max_price']) && $_GET['max_price'] !== '' && $_GET['max_price'] !== '999999999' ? htmlspecialchars($_GET['max_price']) : ''; ?>" min="0">
                    </div>
                </form>

                <!-- Reset Filters -->
                <form method="GET" action="">
                    <button type="submit" class="btn btn-secondary" style="width: 100%; margin-top: 22px;">
                        <i class="fas fa-times"></i> Xóa Lọc
                    </button>
                </form>
            </div>
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
                    $current_time = $_SESSION['current_time'];
                    $discount_start = $row['discount_start'];
                    $discount_end = $row['discount_end'];

                    if ($current_time >= $discount_start && $current_time <= $discount_end) {
                        $price = $row['temporary_price'];
                        $original_price = $row['price'];
                        $is_on_sale = true;
                        $discount_percent = round((($original_price - $price) / $original_price) * 100);
                    } else {
                        $price = $row['price'];
                        $original_price = null;
                        $is_on_sale = false;
                    }
                    ?>
                    <div class="product-card">
                        <!-- Product Image -->
                        <div class="product-image-wrapper">
                            <?php 
                            $image_path = !empty($row['path']) ? htmlspecialchars($row['path']) : 'images/placeholder.png';
                            // Remove 'admin/' prefix if it exists in the path
                            if (strpos($image_path, 'admin/') === 0) {
                                $image_path = substr($image_path, 6);
                            }
                            ?>
                            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2216%22 fill=%22%23999%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                            
                            <!-- Sale Badge -->
                            <?php if ($is_on_sale): ?>
                                <span class="product-badge sale">-<?php echo $discount_percent; ?>%</span>
                            <?php else: ?>
                                <span class="product-badge new">Mới</span>
                            <?php endif; ?>

                            <!-- Wishlist Button -->
                            <button class="product-wishlist" onclick="toggleWishlist(this)">
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

                            <!-- Actions - Default (visible) -->
                            <div class="product-actions" style="position: relative; transform: none; opacity: 1;">
                                <form method="POST" action="add_to_cart.php" style="width: 100%;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-shopping-cart"></i> Thêm
                                    </button>
                                </form>
                                <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="width: 100%;">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </div>
                            
                            <!-- Actions - Hover (slide up) -->
                            <div class="product-actions product-actions-hover">
                                <form method="POST" action="add_to_cart.php" style="width: 100%;">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px;">
                                        <i class="fas fa-shopping-cart"></i> Thêm Vào Giỏ Hàng
                                    </button>
                                </form>
                            </div>
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
        function toggleWishlist(btn) {
            btn.querySelector('i').classList.toggle('far');
            btn.querySelector('i').classList.toggle('fas');
            btn.classList.toggle('active');
        }

        // Sort functionality
        document.querySelector('.sort-select').addEventListener('change', (e) => {
            const sortValue = e.target.value;
            // Add your sorting logic here
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
