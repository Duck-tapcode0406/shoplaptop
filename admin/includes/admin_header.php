<?php
/**
 * Admin Header - Include at top of all admin pages
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/helpers.php';

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
$admin_data_check = $admin_result ? $admin_result->fetch_assoc() : null;

if (!$admin_data_check || $admin_data_check['is_admin'] != 1) {
    header('Location: ../index.php');
    exit();
}

// Get admin info
$admin_info = $conn->prepare("SELECT username, email, avatar FROM user WHERE id = ?");
$admin_info->bind_param('i', $user_id);
$admin_info->execute();
$admin_result = $admin_info->get_result();
$admin_data = $admin_result ? $admin_result->fetch_assoc() : null;

// Fallback if admin data not found
if (!$admin_data) {
    $admin_data = [
        'username' => $_SESSION['username'] ?? 'Admin',
        'email' => '',
        'avatar' => ''
    ];
}

// Current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);

// Get pending orders count once
$pending_orders_result = $conn->query("SELECT COUNT(DISTINCT order_id) as cnt FROM order_details WHERE status = 'pending'");
$pending_orders = 0;
if ($pending_orders_result) {
    $pending_row = $pending_orders_result->fetch_assoc();
    $pending_orders = $pending_row ? (int)$pending_row['cnt'] : 0;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - DNQDH Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6C5CE7;
            --primary-dark: #5849c2;
            --secondary: #a29bfe;
            --success: #00b894;
            --warning: #fdcb6e;
            --danger: #e74c3c;
            --info: #0984e3;
            --dark: #2d3436;
            --light: #f8f9fa;
            --sidebar-width: 260px;
            --header-height: 70px;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #2d3436 0%, #1e272e 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .sidebar-logo img {
            height: 45px;
            filter: brightness(0) invert(1);
        }

        .sidebar-logo span {
            font-size: 22px;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-category {
            padding: 10px 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.4);
            margin-top: 15px;
        }

        .menu-item {
            display: block;
            padding: 14px 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .menu-item.active {
            background: rgba(108, 92, 231, 0.3);
            color: white;
            border-left-color: var(--primary);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .menu-item .badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
        }

        /* Header */
        .admin-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: white;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 999;
        }

        .header-search {
            display: flex;
            align-items: center;
            background: #f0f2f5;
            border-radius: 25px;
            padding: 10px 20px;
            width: 350px;
        }

        .header-search input {
            border: none;
            background: none;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        .header-search i {
            color: #999;
            margin-right: 10px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .header-icon:hover {
            background: var(--primary);
            color: white;
        }

        .header-icon .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 18px;
            height: 18px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .admin-profile:hover {
            background: #f0f2f5;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .admin-name {
            font-weight: 600;
            font-size: 14px;
        }

        .admin-role {
            font-size: 12px;
            color: #999;
        }

        /* Main Content */
        .admin-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }

        .page-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #999;
            margin-top: 5px;
        }

        .page-breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 25px;
            margin-bottom: 25px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 25px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.blue { background: linear-gradient(135deg, #0984e3, #74b9ff); }
        .stat-icon.green { background: linear-gradient(135deg, #00b894, #55efc4); }
        .stat-icon.orange { background: linear-gradient(135deg, #e17055, #fab1a0); }
        .stat-icon.purple { background: linear-gradient(135deg, #6c5ce7, #a29bfe); }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-info p {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
        }

        .stat-change {
            font-size: 12px;
            margin-top: 8px;
        }

        .stat-change.up { color: var(--success); }
        .stat-change.down { color: var(--danger); }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 2px solid #eee;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .data-table .product-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .data-table .product-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(108, 92, 231, 0.4);
        }

        .btn-success { background: var(--success); color: white; }
        .btn-warning { background: var(--warning); color: var(--dark); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-secondary { background: #e9ecef; color: var(--dark); }
        .btn-info { background: var(--info); color: white; }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        /* Status Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.success { background: #d4edda; color: #155724; }
        .status-badge.warning { background: #fff3cd; color: #856404; }
        .status-badge.danger { background: #f8d7da; color: #721c24; }
        .status-badge.info { background: #d1ecf1; color: #0c5460; }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 25px;
        }

        .pagination a, .pagination span {
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            background: white;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-warning { background: #fff3cd; color: #856404; }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.active {
                transform: translateX(0);
            }

            .admin-header {
                left: 0;
            }

            .admin-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <a href="../index.php" class="sidebar-logo">
                <?php if (file_exists('../images/logo.png')): ?>
                <img src="../images/logo.png" alt="Logo" onerror="this.style.display='none'">
                <?php endif; ?>
                <span>DNQDH</span>
            </a>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-category">Menu chính</div>
            <a href="index.php" class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            
            <div class="menu-category">Quản lý</div>
            <a href="products.php" class="menu-item <?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Sản phẩm
            </a>
            <a href="categories.php" class="menu-item <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Danh mục
            </a>
            <a href="orders.php" class="menu-item <?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Đơn hàng
            </a>
            <a href="users.php" class="menu-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Quản lý người dùng
            </a>
            
            <div class="menu-category">Cài đặt</div>
            <a href="suppliers.php" class="menu-item <?php echo $current_page == 'suppliers.php' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i> Nhà cung cấp
            </a>
            <a href="discounts.php" class="menu-item <?php echo $current_page == 'discounts.php' ? 'active' : ''; ?>">
                <i class="fas fa-percent"></i> Khuyến mãi
            </a>
            <a href="settings.php" class="menu-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Cài đặt
            </a>
            
            <div class="menu-category">Tài khoản</div>
            <a href="../index.php" class="menu-item">
                <i class="fas fa-store"></i> Về trang chủ
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </nav>
    </aside>

    <!-- Header -->
    <header class="admin-header">
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm...">
        </div>
        <div class="header-actions">
            <div class="admin-profile">
                <?php 
                $avatar_path = '';
                if (!empty($admin_data['avatar'])) {
                    if (strpos($admin_data['avatar'], 'uploads/avatars/') !== false || strpos($admin_data['avatar'], '../') === 0) {
                        $avatar_path = '../' . ltrim($admin_data['avatar'], '../');
                    } else {
                        $avatar_path = '../uploads/avatars/' . $admin_data['avatar'];
                    }
                    
                    if (!file_exists($avatar_path)) {
                        $avatar_path = '';
                    }
                }
                
                if (empty($avatar_path)) {
                    $username_escaped = urlencode($admin_data['username'] ?? 'Admin');
                    $avatar_path = "https://ui-avatars.com/api/?name={$username_escaped}&background=6c5ce7&color=fff&size=80";
                }
                ?>
                <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Admin" class="admin-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($admin_data['username'] ?? 'Admin'); ?>&background=6c5ce7&color=fff&size=80'">
                <div>
                    <div class="admin-name"><?php echo htmlspecialchars($admin_data['username'] ?? 'Admin'); ?></div>
                    <div class="admin-role">Administrator</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-content">

