<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Lấy số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = "SELECT SUM(od.quantity) as total 
                   FROM `order` o 
                   JOIN `order_details` od ON o.id = od.order_id 
                   WHERE o.customer_id = ? AND od.status = 'pending'";
    $cart_stmt = $conn->prepare($cart_query);
    if ($cart_stmt) {
        $cart_stmt->bind_param('i', $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        if ($row = $cart_result->fetch_assoc()) {
            $cart_count = $row['total'] ?: 0;
        }
        // Giải phóng tài nguyên
        $cart_result->free();
        $cart_stmt->close();
    }
}

// Lấy danh mục sản phẩm (nếu có bảng category)
$categories = [];
$categories_query = "SHOW TABLES LIKE 'category'";
$check_categories = $conn->query($categories_query);
if ($check_categories && $check_categories->num_rows > 0) {
    $cat_query = "SELECT id, name FROM category WHERE is_active = 1 ORDER BY sort_order, name LIMIT 10";
    $cat_result = $conn->query($cat_query);
    if ($cat_result) {
        while ($cat = $cat_result->fetch_assoc()) {
            $categories[] = $cat;
        }
    }
}

// Lấy địa điểm hiện tại (từ session hoặc mặc định)
$current_location = isset($_SESSION['user_location']) ? $_SESSION['user_location'] : 'Hồ Chí Minh';

// Check if user is admin (for view-only mode)
$is_admin_view_mode = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $admin_check = $conn->prepare("SELECT is_admin FROM user WHERE id = ?");
    $admin_check->bind_param('i', $user_id);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    $admin_data = $admin_result ? $admin_result->fetch_assoc() : null;
    $is_admin_view_mode = ($admin_data && $admin_data['is_admin'] == 1);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern E-Commerce Shop">
    <title>DNQDH Shop - Cửa Hàng Trực Tuyến</title>
    
           <!-- CSS Links -->
           <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
           <link rel="stylesheet" href="/shop/css/main.css">
           <link rel="stylesheet" href="/shop/css/header.css">
           <!-- Notification System (tương tự Bookstore) -->
           <script src="/shop/js/notification.js"></script>
           <?php
               $currentPage = basename($_SERVER['PHP_SELF']);
               if (in_array($currentPage, ['checkout.php','addresses.php'])) :
           ?>
           <!-- Location verification only on checkout / addresses pages -->
           <script src="/shop/js/location-verification.js"></script>
           <?php endif; ?>
</head>
<body>
    <!-- Top Information Bar -->
    <div class="header-top-bar">
        <div class="header-top-content">
            <div class="header-top-left">
                <span>
                    <i class="fas fa-check-circle"></i>
                    Sản phẩm Chính hãng - Xuất VAT đầy đủ
                </span>
                <span>
                    <i class="fas fa-truck"></i>
                    Giao nhanh - Miễn phí cho đơn 300k
                </span>
                <span>
                    <i class="fas fa-sync-alt"></i>
                    Thu cũ giá ngon – Lên đời tiết kiệm
                </span>
            </div>
            <div class="header-top-right">
                <a href="addresses.php">
                    <i class="fas fa-store"></i>
                    Cửa hàng gần bạn
                </a>
                <div class="header-top-divider"></div>
                <a href="history.php">
                    <i class="fas fa-clipboard-list"></i>
                    Tra cứu đơn hàng
                </a>
                <div class="header-top-divider"></div>
                <a href="tel:18002097">
                    <i class="fas fa-phone"></i>
                    1800 2097
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <div class="header-main">
        <div class="header-main-content">
            <!-- Logo -->
            <a href="index.php" class="header-logo">
                <img src="images/logo.png" alt="DNQDH Shop" class="logo-img">
                <span class="logo-text">DNQDH</span>
            </a>

            <!-- Category Dropdown -->
            <div class="header-dropdown" id="category-dropdown">
                <button class="header-category-btn" onclick="toggleDropdown('category-dropdown')">
                    <i class="fas fa-th"></i>
                    Danh mục
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="category-mega-menu">
                    <div class="category-menu-columns">
                        <!-- Column 1: Loại PC -->
                        <div class="category-menu-column">
                            <h4>Loại PC</h4>
                            <ul>
                                <li><a href="product.php?category=build-pc">Build PC</a></li>
                                <li><a href="product.php?category=prebuilt">Cấu hình sẵn</a></li>
                                <li><a href="product.php?category=all-in-one">All In One</a></li>
                                <li><a href="product.php?category=pc-set">PC bộ</a></li>
                            </ul>
                            <div class="category-subsection">
                                <div class="category-subsection-title">Chọn PC theo nhu cầu</div>
                                <ul>
                                    <li><a href="product.php?category=gaming-pc">Gaming</a></li>
                                    <li><a href="product.php?category=graphics-pc">Đồ họa</a></li>
                                    <li><a href="product.php?category=office-pc">Văn phòng</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Column 2: Linh kiện máy tính -->
                        <div class="category-menu-column">
                            <h4>Linh kiện máy tính</h4>
                            <ul>
                                <li><a href="product.php?category=cpu">CPU</a></li>
                                <li><a href="product.php?category=mainboard">Main</a></li>
                                <li><a href="product.php?category=ram">RAM</a></li>
                                <li><a href="product.php?category=storage">Ổ cứng</a></li>
                                <li><a href="product.php?category=psu">Nguồn</a></li>
                                <li><a href="product.php?category=vga">VGA</a></li>
                                <li><a href="product.php?category=cooling">Tản nhiệt</a></li>
                                <li><a href="product.php?category=case">Case</a></li>
                                <li><a href="product.php?category=components">Xem tất cả</a></li>
                            </ul>
                        </div>

                        <!-- Column 3: Chọn màn hình theo hãng -->
                        <div class="category-menu-column">
                            <h4>Chọn màn hình theo hãng</h4>
                            <ul>
                                <li><a href="product.php?brand=asus">ASUS</a></li>
                                <li><a href="product.php?brand=samsung">Samsung</a></li>
                                <li><a href="product.php?brand=dell">DELL</a></li>
                                <li><a href="product.php?brand=lg">LG</a></li>
                                <li><a href="product.php?brand=msi">MSI</a></li>
                                <li><a href="product.php?brand=acer">Acer</a></li>
                                <li><a href="product.php?brand=xiaomi">Xiaomi</a></li>
                                <li><a href="product.php?brand=viewsonic">ViewSonic</a></li>
                                <li><a href="product.php?brand=philips">Philips</a></li>
                                <li><a href="product.php?brand=aoc">AOC</a></li>
                                <li><a href="product.php?brand=dahua">Dahua</a></li>
                                <li><a href="product.php?brand=koorui">KOORUI</a></li>
                            </ul>
                        </div>

                        <!-- Column 4: Chọn màn hình theo nhu cầu -->
                        <div class="category-menu-column">
                            <h4>Chọn màn hình theo nhu cầu</h4>
                            <ul>
                                <li><a href="product.php?monitor=gaming">Gaming</a></li>
                                <li><a href="product.php?monitor=office">Văn phòng</a></li>
                                <li><a href="product.php?monitor=graphics">Đồ họa</a></li>
                                <li><a href="product.php?monitor=programming">Lập trình</a></li>
                                <li><a href="product.php?monitor=portable">Màn hình di động</a></li>
                                <li><a href="product.php?monitor=arm">Arm màn hình</a></li>
                                <li><a href="product.php?category=monitors">Xem tất cả</a></li>
                            </ul>
                        </div>

                        <!-- Column 5: Gaming Gear -->
                        <div class="category-menu-column">
                            <h4>Gaming Gear</h4>
                            <ul>
                                <li><a href="product.php?category=playstation">PlayStation</a></li>
                                <li><a href="product.php?category=rog-ally">ROG Ally</a></li>
                                <li><a href="product.php?category=gaming-keyboard">Bàn phím Gaming</a></li>
                                <li><a href="product.php?category=gaming-mouse">Chuột chơi game</a></li>
                                <li><a href="product.php?category=gaming-headset">Tai nghe Gaming</a></li>
                                <li><a href="product.php?category=game-controller">Tay cầm chơi Game</a></li>
                                <li><a href="product.php?category=gaming">Xem tất cả</a></li>
                            </ul>
                            <div class="category-subsection">
                                <div class="category-subsection-title">Thiết bị văn phòng</div>
                                <ul>
                                    <li><a href="product.php?category=printer">Máy in</a></li>
                                    <li><a href="product.php?category=software">Phần mềm</a></li>
                                    <li><a href="product.php?category=desk-decor">Decor bàn làm việc</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Filter Section -->
                    <div class="category-filter-section" style="grid-column: 1 / -1; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                        <h4 style="margin-bottom: 15px; color: #E11B1E; font-size: 14px; font-weight: bold; padding-bottom: 8px; border-bottom: 2px solid #E11B1E;">
                            <i class="fas fa-filter" style="margin-right: 8px;"></i>Lọc Theo Giá
                        </h4>
                        <form method="GET" action="index.php" class="filters-grid-form">
                            <div class="grid grid-3" style="align-items:end; gap: 12px;">
                                <div>
                                    <div class="filter-group">
                                        <label for="category_min_price"><i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Giá tối thiểu</label>
                                        <select name="min_price" id="category_min_price" class="filter-group input">
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
                                        <label for="category_max_price"><i class="fas fa-dollar-sign" style="margin-right: 8px;"></i>Giá tối đa</label>
                                        <select name="max_price" id="category_max_price" class="filter-group input">
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
                                    <button type="submit" class="btn btn-primary" style="height:44px;min-width:120px;">
                                        <i class="fas fa-sliders-h"></i> Lọc
                                    </button>
                                    <a href="index.php" class="btn btn-secondary" style="height:44px;min-width:120px;display:flex;align-items:center;justify-content:center;text-decoration:none;">
                                        × Xóa Lọc
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Location Selector -->
            <div class="header-dropdown" id="location-dropdown">
                <button class="header-location-btn" onclick="toggleDropdown('location-dropdown')">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="current-location"><?php echo htmlspecialchars($current_location); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="header-dropdown-menu">
                    <a href="#" class="header-dropdown-item" onclick="setLocation('Hồ Chí Minh'); return false;">
                        <i class="fas fa-map-marker-alt"></i>
                        Hồ Chí Minh
                    </a>
                    <a href="#" class="header-dropdown-item" onclick="setLocation('Hà Nội'); return false;">
                        <i class="fas fa-map-marker-alt"></i>
                        Hà Nội
                    </a>
                    <a href="#" class="header-dropdown-item" onclick="setLocation('Đà Nẵng'); return false;">
                        <i class="fas fa-map-marker-alt"></i>
                        Đà Nẵng
                    </a>
                    <a href="#" class="header-dropdown-item" onclick="setLocation('Huế'); return false;">
                        <i class="fas fa-map-marker-alt"></i>
                        Huế
                    </a>
                    <a href="#" class="header-dropdown-item" onclick="setLocation('Cần Thơ'); return false;">
                        <i class="fas fa-map-marker-alt"></i>
                        Cần Thơ
                    </a>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="header-search">
                <form action="index.php" method="GET" style="display: flex; width: 100%;">
                    <input type="text" name="search" placeholder="Bạn muốn mua gì hôm nay?" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <!-- Shopping Cart (Hidden for admin view mode) -->
            <?php if (!$is_admin_view_mode): ?>
            <a href="cart.php" class="header-cart">
                <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                <span>Giỏ hàng</span>
                <?php if ($cart_count > 0): ?>
                    <span class="header-cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <!-- User Profile -->
            <div class="header-dropdown" id="user-dropdown">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="header-user" onclick="toggleDropdown('user-dropdown')">
                        <i class="fas fa-user-circle" style="font-size: 20px;"></i>
                        <span class="header-user-name">
                            <?php 
                            if ($is_admin_view_mode) {
                                echo htmlspecialchars($_SESSION['username'] ?? 'Admin') . ' <span style="font-size: 12px; color: #999;">(Admin View)</span>';
                            } else {
                                echo htmlspecialchars($_SESSION['username'] ?? 'Tài khoản');
                            }
                            ?>
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="header-dropdown-menu user-dropdown-menu">
                        <?php if ($is_admin_view_mode): ?>
                            <!-- Admin View Mode - Only show admin options -->
                            <div class="user-dropdown-header">
                                <?php echo htmlspecialchars($_SESSION['username']); ?> <span style="color: #999; font-size: 11px;">(Admin)</span>
                            </div>
                            <div style="padding: 10px 15px; background: #fff3cd; border-bottom: 1px solid #eee; font-size: 12px; color: #856404;">
                                <i class="fas fa-info-circle"></i> Chế độ xem admin - Chỉ xem, không thể mua hàng
                            </div>
                            <a href="admin/index.php" class="user-dropdown-item">
                                <i class="fas fa-tachometer-alt"></i>
                                Quay về trang admin
                            </a>
                            <a href="logout.php" class="user-dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Đăng xuất
                            </a>
                        <?php else: ?>
                            <!-- Normal User Mode -->
                            <div class="user-dropdown-header">
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </div>
                            <a href="user.php" class="user-dropdown-item">
                                <i class="fas fa-user"></i>
                                Thông tin tài khoản
                            </a>
                            <a href="addresses.php" class="user-dropdown-item">
                                <i class="fas fa-map-marker-alt"></i>
                                Địa chỉ giao hàng
                            </a>
                            <a href="history.php" class="user-dropdown-item">
                                <i class="fas fa-history"></i>
                                Đơn hàng của tôi
                            </a>
                            <a href="wishlist.php" class="user-dropdown-item">
                                <i class="fas fa-heart"></i>
                                Sản phẩm yêu thích
                            </a>
                            <a href="logout.php" class="user-dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                Đăng xuất
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="header-user">
                        <i class="fas fa-user-circle" style="font-size: 20px;"></i>
                        <span>Đăng nhập</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Category Menu Bar -->
    <div class="header-category-menu">
        <div class="header-category-menu-content">
            <a href="product.php?category=laptop">Laptop</a>
            <a href="product.php?category=pc">PC & Máy Tính</a>
            <a href="product.php?category=monitor">Màn Hình</a>
            <a href="product.php?category=keyboard">Bàn Phím</a>
            <a href="product.php?category=mouse">Chuột</a>
            <a href="product.php?category=headphone">Tai Nghe</a>
            <a href="product.php?category=webcam">Webcam</a>
            <a href="product.php?category=speaker">Loa</a>
            <a href="product.php?category=storage">Ổ Cứng</a>
            <a href="product.php?category=ram">RAM</a>
            <a href="product.php?category=graphics">Card Đồ Họa</a>
            <a href="product.php?category=accessories">Phụ Kiện</a>
        </div>
    </div>

    <script>
        // Toggle dropdown menus
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll('.header-dropdown');
            
            // Close all other dropdowns
            allDropdowns.forEach(d => {
                if (d.id !== id) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.header-dropdown')) {
                document.querySelectorAll('.header-dropdown').forEach(d => {
                    d.classList.remove('active');
                });
            }
        });

        // Set location
        function setLocation(location) {
            document.getElementById('current-location').textContent = location;
            // You can save to session via AJAX if needed
            fetch('api/save_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ location: location })
            });
            toggleDropdown('location-dropdown');
        }

        // Sticky header on scroll
        const headerMain = document.querySelector('.header-main');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                headerMain.style.position = 'sticky';
                headerMain.style.top = '0';
                headerMain.style.zIndex = '1000';
            } else {
                headerMain.style.position = 'relative';
            }
        });
    </script>
</body>
</html>
