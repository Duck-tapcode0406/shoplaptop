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
    $stmt = $conn->prepare($cart_query);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $cart_count = $row['total'] ?: 0;
        }
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern E-Commerce Shop">
    <title>ModernShop - Cửa Hàng Trực Tuyến Hiện Đại</title>
    
    <!-- CSS Links -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/shop/css/main.css">
    <style>
        /* Modern Header Styles */
        .header-top-bar {
            background: #f8f9fa;
            color: #636e72;
            padding: 8px 0;
            font-size: 13px;
            border-bottom: 1px solid #e9ecef;
        }

        .header-top-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-top-left {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-right: auto;
        }

        .header-top-right {
            margin-left: auto;
        }

        .header-top-left span {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #636e72;
        }

        .header-top-left i {
            font-size: 14px;
            color: #6c757d;
        }

        .header-top-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-top-right a {
            color: #636e72;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.3s;
        }

        .header-top-right a:hover {
            color: #4169E1;
        }

        .header-top-divider {
            width: 1px;
            height: 16px;
            background: #dee2e6;
        }

        .header-main {
            background: #ffffff;
            padding: 20px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-logo {
            font-size: 28px;
            font-weight: bold;
            color: #4169E1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            transition: color 0.3s;
        }

        .header-logo:hover {
            color: #1E90FF;
        }

        .header-logo .logo-s {
            font-size: 32px;
            transform: rotate(-15deg);
            display: inline-block;
            color: #4169E1;
        }

        .header-category-btn,
        .header-location-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .header-category-btn:hover,
        .header-location-btn:hover {
            background: #e9ecef;
            border-color: #4169E1;
            color: #4169E1;
        }

        .header-search {
            flex: 1;
            max-width: 600px;
            position: relative;
        }

        .header-search input {
            width: 100%;
            padding: 12px 50px 12px 20px;
            border: 2px solid #dee2e6;
            border-radius: 50px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .header-search input:focus {
            border-color: #4169E1;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.1);
        }

        .header-search button {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #4169E1;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .header-search button:hover {
            background: #1E90FF;
            transform: translateY(-50%) scale(1.05);
        }

        .header-cart,
        .header-user {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #495057;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s;
            position: relative;
        }

        .header-cart:hover,
        .header-user:hover {
            background: #f8f9fa;
            color: #4169E1;
        }

        .header-cart i,
        .header-user i {
            font-size: 20px;
        }

        .header-cart-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #FF6B00;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }

        .header-user-name {
            font-size: 14px;
            font-weight: 500;
        }

        /* Dropdown Menus */
        .header-dropdown {
            position: relative;
        }

        .header-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 6px;
            margin-top: 8px;
            display: none;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }

        .header-dropdown.active .header-dropdown-menu {
            display: block;
        }

        .header-dropdown-item {
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            display: block;
            transition: background 0.2s;
        }

        .header-dropdown-item:hover {
            background: #f5f5f5;
        }

        .header-dropdown-item i {
            margin-right: 8px;
            color: #666;
        }

        /* Category Mega Menu */
        .category-mega-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 900px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 6px;
            margin-top: 8px;
            display: none;
            z-index: 1000;
            padding: 20px;
        }

        .header-dropdown.active .category-mega-menu {
            display: block;
        }

        .category-menu-columns {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 30px;
        }

        .category-menu-column h4 {
            font-size: 14px;
            font-weight: bold;
            color: #E11B1E;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #E11B1E;
        }

        .category-menu-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .category-menu-column ul li {
            margin-bottom: 8px;
        }

        .category-menu-column ul li a {
            color: #333;
            text-decoration: none;
            font-size: 13px;
            display: block;
            padding: 4px 0;
            transition: color 0.2s;
        }

        .category-menu-column ul li a:hover {
            color: #E11B1E;
        }

        .category-subsection {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .category-subsection-title {
            font-size: 12px;
            font-weight: bold;
            color: #666;
            margin-bottom: 8px;
        }

        /* User Dropdown */
        .user-dropdown-menu {
            right: 0;
            left: auto;
            min-width: 220px;
            background: #E11B1E;
            border: 2px solid #C4161C;
        }

        .user-dropdown-header {
            padding: 15px;
            border-bottom: 2px solid #C4161C;
            font-weight: bold;
            color: white;
            background: #C4161C;
        }

        .user-dropdown-item {
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s;
        }

        .user-dropdown-item:hover {
            background: #C4161C;
        }

        .user-dropdown-item i {
            color: white;
            width: 18px;
        }

        /* Category Menu Bar */
        .header-category-menu {
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 12px 0;
        }

        .header-category-menu-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 30px;
            overflow-x: auto;
        }

        .header-category-menu a {
            color: #495057;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            padding: 8px 0;
            white-space: nowrap;
            transition: color 0.3s;
            position: relative;
        }

        .header-category-menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #4169E1;
            transition: width 0.3s;
        }

        .header-category-menu a:hover {
            color: #4169E1;
        }

        .header-category-menu a:hover::after {
            width: 100%;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-top-content {
                flex-direction: column;
                text-align: center;
            }

            .header-main-content {
                flex-wrap: wrap;
            }

            .header-search {
                order: 3;
                width: 100%;
                max-width: 100%;
            }

            .header-category-btn,
            .header-location-btn {
                font-size: 12px;
                padding: 8px 10px;
            }

            .category-mega-menu {
                min-width: 100vw;
                left: -20px;
                right: -20px;
                border-radius: 0;
            }

            .category-menu-columns {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 1024px) {
            .category-mega-menu {
                min-width: 700px;
            }

            .category-menu-columns {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
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
                <span>ModernShop</span>
                <span class="logo-s">S</span>
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

            <!-- Shopping Cart -->
            <a href="cart.php" class="header-cart">
                <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                <span>Giỏ hàng</span>
                <?php if ($cart_count > 0): ?>
                    <span class="header-cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>

            <!-- User Profile -->
            <div class="header-dropdown" id="user-dropdown">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="header-user" onclick="toggleDropdown('user-dropdown')">
                        <i class="fas fa-user-circle" style="font-size: 20px;"></i>
                        <span class="header-user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Tài khoản'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="header-dropdown-menu user-dropdown-menu">
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
                        <a href="logout.php" class="user-dropdown-item" style="color: #ff6b6b;">
                            <i class="fas fa-sign-out-alt"></i>
                            Đăng xuất
                        </a>
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
