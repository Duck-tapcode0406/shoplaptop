<?php
$page_title = 'Quản lý người dùng';
require_once 'includes/admin_header.php';

$success_message = '';
$error_message = '';

// Handle delete
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (validateCSRFToken($_GET['token'])) {
        $user_id = intval($_GET['delete']);
        // Don't delete yourself
        if ($user_id != $_SESSION['user_id']) {
            $conn->query("DELETE FROM user WHERE id = $user_id");
            $success_message = 'Đã xóa người dùng!';
        } else {
            $error_message = 'Không thể xóa tài khoản của chính mình!';
        }
    } else {
        $error_message = 'Token không hợp lệ!';
    }
}

// Handle toggle admin
if (isset($_GET['toggle_admin']) && isset($_GET['token'])) {
    if (validateCSRFToken($_GET['token'])) {
        $user_id = intval($_GET['toggle_admin']);
        if ($user_id != $_SESSION['user_id']) {
            $current = $conn->query("SELECT is_admin FROM user WHERE id = $user_id")->fetch_assoc();
            $new_status = $current['is_admin'] == 1 ? 0 : 1;
            $conn->query("UPDATE user SET is_admin = $new_status WHERE id = $user_id");
            $success_message = $new_status == 1 ? 'Đã cấp quyền Admin!' : 'Đã hủy quyền Admin!';
        } else {
            $error_message = 'Không thể thay đổi quyền của chính mình!';
        }
    } else {
        $error_message = 'Token không hợp lệ!';
    }
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$admin_filter = isset($_GET['admin']) ? intval($_GET['admin']) : -1;

// Build where clause
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (username LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}
if ($admin_filter >= 0) {
    $where .= " AND is_admin = $admin_filter";
}

// Get total
$total_result = $conn->query("SELECT COUNT(*) as cnt FROM user $where");
$total = $total_result ? $total_result->fetch_assoc()['cnt'] : 0;
$total_pages = ceil($total / $per_page);

// Get users
$users = $conn->query("
    SELECT * FROM user
    $where
    ORDER BY id DESC
    LIMIT $per_page OFFSET $offset
");

// Get stats
$total_users = $conn->query("SELECT COUNT(*) as cnt FROM user")->fetch_assoc()['cnt'];
$total_admins = $conn->query("SELECT COUNT(*) as cnt FROM user WHERE is_admin = 1")->fetch_assoc()['cnt'];

$csrf_token = generateCSRFToken();
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Quản lý người dùng</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Người dùng</span>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        <a href="user_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm người dùng
        </a>
    </div>
</div>

<?php if ($error_message): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
</div>
<?php endif; ?>

<?php if ($success_message): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($total_users); ?></h3>
            <p>Tổng người dùng</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($total_admins); ?></h3>
            <p>Admin</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <i class="fas fa-user"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($total_users - $total_admins); ?></h3>
            <p>Khách hàng</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 25px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" style="width: 300px;" 
               placeholder="Tìm theo tên, email, SĐT..." value="<?php echo htmlspecialchars($search); ?>">
        
        <select name="admin" class="form-control" style="width: auto;">
            <option value="-1">Tất cả</option>
            <option value="1" <?php echo $admin_filter == 1 ? 'selected' : ''; ?>>Admin</option>
            <option value="0" <?php echo $admin_filter == 0 ? 'selected' : ''; ?>>Khách hàng</option>
        </select>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách người dùng (<?php echo $total; ?>)</h3>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Người dùng</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Vai trò</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users && $users->num_rows > 0): ?>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <?php 
                            $avatar = $user['avatar'] ?? '';
                            if ($avatar && file_exists('../' . $avatar)) {
                                $avatar_url = '../' . $avatar;
                            } else {
                                $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=6c5ce7&color=fff';
                            }
                            ?>
                            <img src="<?php echo $avatar_url; ?>" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <div>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span class="status-badge warning" style="font-size: 10px;">Bạn</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                    <td>
                        <?php if ($user['is_admin'] == 1): ?>
                        <span class="status-badge danger">Admin</span>
                        <?php else: ?>
                        <span class="status-badge info">Khách hàng</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-warning btn-icon" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?toggle_admin=<?php echo $user['id']; ?>&token=<?php echo $csrf_token; ?>" 
                               class="btn btn-sm btn-<?php echo $user['is_admin'] ? 'secondary' : 'success'; ?> btn-icon"
                               title="<?php echo $user['is_admin'] ? 'Hủy quyền Admin' : 'Cấp quyền Admin'; ?>"
                               onclick="return confirm('<?php echo $user['is_admin'] ? 'Hủy quyền Admin?' : 'Cấp quyền Admin?'; ?>')">
                                <i class="fas fa-user-shield"></i>
                            </a>
                            <a href="?delete=<?php echo $user['id']; ?>&token=<?php echo $csrf_token; ?>" 
                               class="btn btn-sm btn-danger btn-icon" title="Xóa"
                               onclick="return confirmDelete('Xóa người dùng này?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        Không tìm thấy người dùng nào
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($total_pages > 1): ?>
    <div class="pagination" style="padding: 20px; display: flex; justify-content: center; gap: 5px;">
        <?php
        $query_string = http_build_query(array_filter([
            'search' => $search,
            'admin' => $admin_filter >= 0 ? $admin_filter : null
        ]));
        ?>
        
        <?php if ($page > 1): ?>
        <a href="?page=1&<?php echo $query_string; ?>" class="btn btn-sm btn-secondary">&laquo;</a>
        <a href="?page=<?php echo $page - 1; ?>&<?php echo $query_string; ?>" class="btn btn-sm btn-secondary">&lsaquo;</a>
        <?php endif; ?>
        
        <?php
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
        <a href="?page=<?php echo $i; ?>&<?php echo $query_string; ?>" 
           class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>&<?php echo $query_string; ?>" class="btn btn-sm btn-secondary">&rsaquo;</a>
        <a href="?page=<?php echo $total_pages; ?>&<?php echo $query_string; ?>" class="btn btn-sm btn-secondary">&raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>



