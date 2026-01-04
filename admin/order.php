<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
include '../includes/db.php';

// Lấy các giá trị lọc từ form
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$customerFilter = isset($_GET['customer']) ? $_GET['customer'] : '';
$productFilter = isset($_GET['product']) ? $_GET['product'] : '';
$orderBy = isset($_GET['order_by']) ? $_GET['order_by'] : 'o.datetime DESC';

// Lấy số trang hiện tại
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 11; // Số đơn hàng mỗi trang
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn với các điều kiện lọc
$query = "SELECT 
            o.id AS order_id, 
            u.username AS customer_name, 
            o.datetime AS order_date, 
            p.name AS product_name, 
            od.quantity, 
            od.price, 
            (od.quantity * od.price) AS total_price, 
            od.status AS order_status,
            od.color_name,  -- Thêm màu sắc
            od.configuration_name  -- Thêm cấu hình
          FROM `order` o
          JOIN order_details od ON o.id = od.order_id
          JOIN product p ON od.product_id = p.id
          JOIN user u ON o.customer_id = u.id
          WHERE 1=1"; // Điều kiện mặc định để thêm các lọc khác

// Thêm điều kiện lọc trạng thái
if ($statusFilter != 'all') {
    $query .= " AND od.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

// Thêm điều kiện lọc theo tên khách hàng
if (!empty($customerFilter)) {
    $query .= " AND u.username LIKE '%" . $conn->real_escape_string($customerFilter) . "%'";
}

// Thêm điều kiện lọc theo sản phẩm
if (!empty($productFilter)) {
    $query .= " AND p.name LIKE '%" . $conn->real_escape_string($productFilter) . "%'";
}

// Thêm sắp xếp
$query .= " ORDER BY $orderBy";

// Thêm phân trang
$query .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($query);

// Lấy tổng số đơn hàng để tính số trang
$countQuery = "SELECT COUNT(DISTINCT o.id) AS total_orders
               FROM `order` o
               JOIN order_details od ON o.id = od.order_id
               JOIN product p ON od.product_id = p.id
               JOIN user u ON o.customer_id = u.id
               WHERE 1=1";

if ($statusFilter != 'all') {
    $countQuery .= " AND od.status = '" . $conn->real_escape_string($statusFilter) . "'";
}

if (!empty($customerFilter)) {
    $countQuery .= " AND u.username LIKE '%" . $conn->real_escape_string($customerFilter) . "%'";
}

if (!empty($productFilter)) {
    $countQuery .= " AND p.name LIKE '%" . $conn->real_escape_string($productFilter) . "%'";
}

$countResult = $conn->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalOrders = $countRow['total_orders'];
$totalPages = ceil($totalOrders / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn Hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }

        .sidebar {
            width: 200px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar h2 {
            color: white;
            margin-bottom: 40px;
            text-align: center;
        }

        .sidebar .nav {
            list-style: none;
            padding-left: 0;
        }

        .sidebar .nav-item {
            margin: 15px 0;
        }

        .sidebar .nav-link {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: block;
        }

        .sidebar .nav-link:hover {
            background-color: #575757;
            border-radius: 5px;
            padding: 10px;
        }

        .content {
            margin-left: 200px;
            padding: 20px;
        }

        .form-control, .form-select {
            background-color: #333333;
            color: white;
            border: 1px solid #444444;
        }

        .form-control::placeholder {
            color: #bbb;
        }

        .table-dark th, .table-dark td {
            color: #e0e0e0;
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid #444444;
        }

        .table-hover tbody tr:hover {
            background-color: #444444;
        }

        .pagination {
            justify-content: center;
            margin-top: 30px;
        }

        .pagination .page-link {
            background-color: #333333;
            color: #e0e0e0;
            border-color: #444444;
        }

        .pagination .page-link:hover {
            background-color: #444444;
            color: #ffffff;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #333333;
            color: #666666;
            border-color: #444444;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Quản lý Hệ thống</h2>
        <ul class="nav">
            <li class="nav-item"><a class="nav-link" href="index.php">Trang Chủ</a></li>
            <li class="nav-item"><a class="nav-link active" href="order.php">Quản lý đơn hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="product.php">Quản lý sản phẩm</a></li>
            <li class="nav-item"><a class="nav-link" href="customers.php">Quản lý khách hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="role.php">Cấp Quyền</a></li>
            <li class="nav-item"><a class="nav-link" href="supplier.php">Nhà cung cấp</a></li>
            <li class="nav-item"><a class="nav-link" href="change_price.php">Đổi giá trong ngày</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Đăng xuất</a></li>
        </ul>
    </div>

    <!-- Nội dung chính -->
    <div class="content">
        <h2 class="text-center mb-4">Quản lý đơn hàng</h2>

        <!-- Bộ lọc -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="all" <?php if ($statusFilter == 'all') echo 'selected'; ?>>Tất cả trạng thái</option>
                        <option value="paid" <?php if ($statusFilter == 'paid') echo 'selected'; ?>>Đã thanh toán</option>
                        <option value="pending" <?php if ($statusFilter == 'pending') echo 'selected'; ?>>Chờ thanh toán</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="customer" class="form-control" placeholder="Tìm theo tên khách hàng" value="<?php echo htmlspecialchars($customerFilter); ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="product" class="form-control" placeholder="Tìm theo tên sản phẩm" value="<?php echo htmlspecialchars($productFilter); ?>">
                </div>
                <div class="col-md-3">
                    <select name="order_by" class="form-select">
                        <option value="o.datetime DESC" <?php if ($orderBy == 'o.datetime DESC') echo 'selected'; ?>>Mới nhất</option>
                        <option value="o.datetime ASC" <?php if ($orderBy == 'o.datetime ASC') echo 'selected'; ?>>Cũ nhất</option>
                        <option value="total_price DESC" <?php if ($orderBy == 'total_price DESC') echo 'selected'; ?>>Hóa đơn từ cao đến thấp</option>
                        <option value="total_price ASC" <?php if ($orderBy == 'total_price ASC') echo 'selected'; ?>>Hóa đơn từ thấp đến cao</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="?" class="btn btn-secondary">Xóa bộ lọc</a>
                </div>
            </div>
        </form>

        <!-- Bảng hiển thị đơn hàng -->
        <table class="table table-bordered table-striped table-dark">
            <thead>
            <tr>
                <th>ID Đơn hàng</th>
                <th>Tên khách hàng</th>
                <th>Ngày đặt hàng</th>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Tổng cộng</th>
                <th>Màu sắc</th>
                <th>Cấu hình</th>
                <th>Trạng thái</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo number_format($row['price'], 0, ',', '.') . ' VND'; ?></td>
                        <td><?php echo number_format($row['total_price'], 0, ',', '.') . ' VND'; ?></td>
                        <td><?php echo htmlspecialchars($row['color_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['configuration_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['order_status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">Không có đơn hàng nào</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <div class="text-center">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?php if ($page <= 1) echo 'enabled'; ?>">
                        <a class="page-link" href="?page=1&status=<?php echo $statusFilter; ?>&customer=<?php echo htmlspecialchars($customerFilter); ?>&product=<?php echo htmlspecialchars($productFilter); ?>&order_by=<?php echo $orderBy; ?>">Đầu</a>
                    </li>
                    <li class="page-item <?php if ($page <= 1) echo 'enabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&customer=<?php echo htmlspecialchars($customerFilter); ?>&product=<?php echo htmlspecialchars($productFilter); ?>&order_by=<?php echo $orderBy; ?>">Trước</a>
                    </li>
                    <li class="page-item <?php if ($page >= $totalPages) echo 'enabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&customer=<?php echo htmlspecialchars($customerFilter); ?>&product=<?php echo htmlspecialchars($productFilter); ?>&order_by=<?php echo $orderBy; ?>">Tiếp</a>
                    </li>
                    <li class="page-item <?php if ($page >= $totalPages) echo 'enabled'; ?>">
                        <a class="page-link" href="?page=<?php echo $totalPages; ?>&status=<?php echo $statusFilter; ?>&customer=<?php echo htmlspecialchars($customerFilter); ?>&product=<?php echo htmlspecialchars($productFilter); ?>&order_by=<?php echo $orderBy; ?>">Cuối</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

</body>
</html>

<?php $conn->close(); ?>
