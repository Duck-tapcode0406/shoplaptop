<?php
$page_title = 'Quản lý đơn hàng';
require_once 'includes/admin_header.php';

$success_message = '';
$error_message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRFPost()) {
        $error_message = 'Token không hợp lệ!';
    } else {
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['new_status'];
        
        $allowed_status = ['pending', 'processing', 'shipped', 'paid', 'cancelled'];
        if (in_array($new_status, $allowed_status)) {
            $stmt = $conn->prepare("UPDATE order_details SET status = ? WHERE order_id = ?");
            $stmt->bind_param('si', $new_status, $order_id);
            if ($stmt->execute()) {
                $success_message = 'Cập nhật trạng thái thành công!';
            }
        }
    }
}

// Pagination & Filters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where = "WHERE 1=1";
if ($status_filter) {
    $where .= " AND od.status = '$status_filter'";
}
if ($search) {
    $where .= " AND (u.username LIKE '%$search%' OR u.email LIKE '%$search%' OR o.id LIKE '%$search%')";
}
if ($date_from) {
    $where .= " AND DATE(o.datetime) >= '$date_from'";
}
if ($date_to) {
    $where .= " AND DATE(o.datetime) <= '$date_to'";
}

// Get total
$total_result = $conn->query("
    SELECT COUNT(DISTINCT o.id) as cnt 
    FROM `order` o
    JOIN order_details od ON o.id = od.order_id
    JOIN user u ON o.customer_id = u.id
    $where
");
$total = $total_result->fetch_assoc()['cnt'];
$total_pages = ceil($total / $per_page);

// Get orders
$orders = $conn->query("
    SELECT o.id, o.datetime, o.customer_id,
           u.username, u.email, u.phone,
           od.status,
           SUM(od.quantity) as total_items,
           SUM(od.quantity * od.price) as total_amount,
           GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as products
    FROM `order` o
    JOIN order_details od ON o.id = od.order_id
    JOIN user u ON o.customer_id = u.id
    JOIN product p ON od.product_id = p.id
    $where
    GROUP BY o.id
    ORDER BY o.datetime DESC
    LIMIT $per_page OFFSET $offset
");

// Get status counts
$status_counts = [];
$status_query = $conn->query("
    SELECT od.status, COUNT(DISTINCT od.order_id) as cnt
    FROM order_details od
    GROUP BY od.status
");
while ($row = $status_query->fetch_assoc()) {
    $status_counts[$row['status']] = $row['cnt'];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Quản lý đơn hàng</h1>
        <div class="page-breadcrumb">
            <a href="index.php">Admin</a>
            <span>/</span>
            <span>Đơn hàng</span>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
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

<!-- Status Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap;">
    <a href="orders.php" class="btn <?php echo !$status_filter ? 'btn-primary' : 'btn-secondary'; ?>">
        Tất cả (<?php echo array_sum($status_counts); ?>)
    </a>
    <a href="?status=pending" class="btn <?php echo $status_filter == 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">
        <i class="fas fa-clock"></i> Chờ xử lý (<?php echo $status_counts['pending'] ?? 0; ?>)
    </a>
    <a href="?status=processing" class="btn <?php echo $status_filter == 'processing' ? 'btn-primary' : 'btn-secondary'; ?>">
        <i class="fas fa-cog"></i> Đang xử lý (<?php echo $status_counts['processing'] ?? 0; ?>)
    </a>
    <a href="?status=shipped" class="btn <?php echo $status_filter == 'shipped' ? 'btn-primary' : 'btn-secondary'; ?>">
        <i class="fas fa-truck"></i> Đang giao (<?php echo $status_counts['shipped'] ?? 0; ?>)
    </a>
    <a href="?status=paid" class="btn <?php echo $status_filter == 'paid' ? 'btn-primary' : 'btn-secondary'; ?>">
        <i class="fas fa-check"></i> Hoàn thành (<?php echo $status_counts['paid'] ?? 0; ?>)
    </a>
    <a href="?status=cancelled" class="btn <?php echo $status_filter == 'cancelled' ? 'btn-primary' : 'btn-secondary'; ?>">
        <i class="fas fa-times"></i> Đã hủy (<?php echo $status_counts['cancelled'] ?? 0; ?>)
    </a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 25px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
        <?php if ($status_filter): ?>
        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
        <?php endif; ?>
        
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, email, mã đơn..." 
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div>
            <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>" placeholder="Từ ngày">
        </div>
        <div>
            <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>" placeholder="Đến ngày">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Lọc
        </button>
    </form>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách đơn hàng (<?php echo $total; ?> đơn)</h3>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Khách hàng</th>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" style="color: var(--primary); font-weight: 600;">
                                #<?php echo $order['id']; ?>
                            </a>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['username']); ?></strong><br>
                            <small style="color: #999;"><?php echo htmlspecialchars($order['email']); ?></small>
                        </td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars(mb_substr($order['products'], 0, 50)); ?>...
                        </td>
                        <td><?php echo $order['total_items']; ?></td>
                        <td style="font-weight: 600; color: var(--primary);">
                            <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>đ
                        </td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($order['datetime'])); ?><br>
                            <small style="color: #999;"><?php echo date('H:i', strtotime($order['datetime'])); ?></small>
                        </td>
                        <td>
                            <?php
                            $status_class = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'info',
                                'paid' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $status_text = [
                                'pending' => 'Chờ xử lý',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao',
                                'paid' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy'
                            ];
                            ?>
                            <span class="status-badge <?php echo $status_class[$order['status']] ?? 'info'; ?>" 
                                  id="status-badge-<?php echo $order['id']; ?>"
                                  data-order-id="<?php echo $order['id']; ?>">
                                <?php echo $status_text[$order['status']] ?? $order['status']; ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-info btn-icon" title="Chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-warning btn-icon" 
                                        title="Cập nhật trạng thái"
                                        id="update-btn-<?php echo $order['id']; ?>"
                                        data-order-id="<?php echo $order['id']; ?>"
                                        data-current-status="<?php echo $order['status']; ?>"
                                        onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="fas fa-shopping-cart" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                            <p style="color: #999;">Không có đơn hàng nào</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php 
        $query_string = http_build_query(array_filter([
            'status' => $status_filter,
            'search' => $search,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]));
        ?>
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&<?php echo $query_string; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <a href="?page=<?php echo $i; ?>&<?php echo $query_string; ?>"
               class="<?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&<?php echo $query_string; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Update Status Modal -->
<div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%;">
        <h3 style="margin-bottom: 20px;">Cập nhật trạng thái đơn hàng</h3>
        <form method="POST">
            <?php echo getCSRFTokenField(); ?>
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="order_id" id="statusOrderId">
            
            <div class="form-group">
                <label class="form-label">Trạng thái mới</label>
                <select name="new_status" id="newStatus" class="form-control">
                    <option value="pending">Chờ xử lý</option>
                    <option value="processing">Đang xử lý</option>
                    <option value="shipped">Đang giao hàng</option>
                    <option value="paid">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" id="cancelStatusBtn" onclick="closeStatusModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" id="submitStatusBtn">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentOrderId = null;

function updateStatus(orderId, currentStatus) {
    currentOrderId = orderId;
    document.getElementById('statusOrderId').value = orderId;
    document.getElementById('newStatus').value = currentStatus;
    document.getElementById('statusModal').style.display = 'flex';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
    currentOrderId = null;
}

document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});

// Handle form submission with AJAX
document.getElementById('statusModal').querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const orderId = document.getElementById('statusOrderId').value;
    const newStatus = document.getElementById('newStatus').value;
    const csrfToken = this.querySelector('input[name="csrf_token"]').value;
    
    // Disable submit button
    const submitBtn = document.getElementById('submitStatusBtn');
    const cancelBtn = document.getElementById('cancelStatusBtn');
    const originalSubmitText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    cancelBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
    
    // Send AJAX request
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('new_status', newStatus);
    formData.append('csrf_token', csrfToken);
    
    fetch('api/update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const newStatus = data.data?.new_status || data.new_status;
            const statusText = data.data?.status_text || data.status_text || '';
            const statusClass = data.data?.status_class || data.status_class || 'info';
            
            // Update status badge in the table
            const statusBadge = document.getElementById('status-badge-' + orderId);
            if (statusBadge) {
                statusBadge.className = 'status-badge ' + statusClass;
                statusBadge.textContent = statusText;
                statusBadge.setAttribute('data-order-id', orderId);
            }
            
            // Update button edit (btn btn-warning btn-icon) với trạng thái mới
            const updateBtn = document.getElementById('update-btn-' + orderId);
            if (updateBtn) {
                updateBtn.setAttribute('data-current-status', newStatus);
                updateBtn.setAttribute('onclick', `updateStatus(${orderId}, '${newStatus}')`);
            }
            
            // Update status tabs counts
            if (data.data?.status_counts || data.status_counts) {
                const counts = data.data?.status_counts || data.status_counts;
                const totalOrders = data.data?.total_orders || Object.values(counts).reduce((a, b) => a + b, 0);
                updateStatusTabs(counts, totalOrders);
            }
            
            // Show success message
            if (typeof showNotification === 'function') {
                showNotification('success', 'Thành công', data.message || 'Cập nhật trạng thái thành công!');
            } else {
                alert(data.message || 'Cập nhật trạng thái thành công!');
            }
            
            // Close modal
            closeStatusModal();
        } else {
            // Show error message
            if (typeof showNotification === 'function') {
                showNotification('error', 'Lỗi', data.message || 'Có lỗi xảy ra!');
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
            
            // Re-enable buttons
            submitBtn.disabled = false;
            cancelBtn.disabled = false;
            submitBtn.innerHTML = originalSubmitText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = 'Có lỗi xảy ra khi cập nhật trạng thái!';
        if (typeof showNotification === 'function') {
            showNotification('error', 'Lỗi', errorMsg);
        } else {
            alert(errorMsg);
        }
        
        // Re-enable buttons
        submitBtn.disabled = false;
        cancelBtn.disabled = false;
        submitBtn.innerHTML = originalSubmitText;
    });
});

function updateStatusTabs(statusCounts, totalOrders) {
    // Update "Tất cả" tab
    const allTab = document.querySelector('a[href="orders.php"]');
    if (allTab) {
        const text = allTab.textContent.replace(/\d+/, totalOrders);
        allTab.innerHTML = text.replace(totalOrders, totalOrders);
        allTab.innerHTML = 'Tất cả (' + totalOrders + ')';
    }
    
    // Update individual status tabs
    const statusTabs = {
        'pending': { selector: 'a[href="?status=pending"]', label: 'Chờ xử lý', icon: '<i class="fas fa-clock"></i>' },
        'processing': { selector: 'a[href="?status=processing"]', label: 'Đang xử lý', icon: '<i class="fas fa-cog"></i>' },
        'shipped': { selector: 'a[href="?status=shipped"]', label: 'Đang giao', icon: '<i class="fas fa-truck"></i>' },
        'paid': { selector: 'a[href="?status=paid"]', label: 'Hoàn thành', icon: '<i class="fas fa-check"></i>' },
        'cancelled': { selector: 'a[href="?status=cancelled"]', label: 'Đã hủy', icon: '<i class="fas fa-times"></i>' }
    };
    
    Object.keys(statusTabs).forEach(status => {
        const tab = document.querySelector(statusTabs[status].selector);
        if (tab) {
            const count = statusCounts[status] || 0;
            tab.innerHTML = statusTabs[status].icon + ' ' + statusTabs[status].label + ' (' + count + ')';
        }
    });
}

function showNotification(type, title, message) {
    // Remove existing notifications
    const existing = document.querySelector('.notification-toast');
    if (existing) {
        existing.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = 'notification-toast ' + type;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
        min-width: 300px;
    `;
    
    if (type === 'success') {
        notification.style.background = '#10b981';
        notification.style.color = 'white';
        notification.innerHTML = '<i class="fas fa-check-circle"></i> <strong>' + title + ':</strong> ' + message;
    } else {
        notification.style.background = '#ef4444';
        notification.style.color = 'white';
        notification.innerHTML = '<i class="fas fa-exclamation-circle"></i> <strong>' + title + ':</strong> ' + message;
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

</script>

<?php require_once 'includes/admin_footer.php'; ?>



