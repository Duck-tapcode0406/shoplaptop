<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Lấy giá trị từ form tìm kiếm và sắp xếp
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'asc';

// Lọc dữ liệu sản phẩm theo tên sản phẩm (nếu có)
$whereClause = '';
if (!empty($search)) {
    $whereClause = "WHERE p.name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Xử lý sắp xếp
$sortOrder = 'ASC';
$orderBy = 'pr.price'; // Mặc định sắp xếp theo giá

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'asc':
            $sortOrder = 'ASC';
            $orderBy = 'pr.price';
            break;
        case 'desc':
            $sortOrder = 'DESC';
            $orderBy = 'pr.price';
            break;
        case 'id_asc':
            $sortOrder = 'ASC';
            $orderBy = 'p.id';
            break;
        case 'id_desc':
            $sortOrder = 'DESC';
            $orderBy = 'p.id';
            break;
    }
}

// Phân trang
$productsPerPage = 6;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

// Cập nhật truy vấn SQL để hỗ trợ tìm kiếm, sắp xếp và phân trang
$sql = "
    SELECT 
        p.id AS product_id,
        i.path, 
        p.name AS product_name,
        pr.price AS price,
        rd.quantity AS total_quantity,
        GROUP_CONCAT(DISTINCT CONCAT(c.color_name, '-', c.configuration_name, '|', c.quantity) SEPARATOR '\n') AS configurations
    FROM 
        product p
    JOIN 
        price pr ON p.id = pr.product_id
    LEFT JOIN 
        receipt_details rd ON p.id = rd.product_id
    LEFT JOIN 
        colors_configuration c ON p.id = c.product_id
    LEFT JOIN 
        image i ON p.id = i.product_id
    $whereClause
    GROUP BY 
        p.id
    ORDER BY 
        $orderBy $sortOrder
    LIMIT $productsPerPage OFFSET $offset
";

$result = $conn->query($sql);

// Lấy tổng số sản phẩm để tính toán phân trang
$totalSql = "
    SELECT COUNT(DISTINCT p.id) AS total
    FROM product p
    JOIN price pr ON p.id = pr.product_id
    LEFT JOIN receipt_details rd ON p.id = rd.product_id
    LEFT JOIN colors_configuration c ON p.id = c.product_id
    LEFT JOIN image i ON p.id = i.product_id
    $whereClause
";

$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();
$totalProducts = $totalRow['total'];
$totalPages = ceil($totalProducts / $productsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #1f1f1f;
            color: #e0e0e0;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar h2 {
            color: #e0e0e0;
            margin-bottom: 40px;
            text-align: center;
        }

        .sidebar .nav-link {
            color: #e0e0e0;
            text-decoration: none;
        }

        .sidebar .nav-link:hover {
            background-color: #333;
            border-radius: 5px;
            padding: 10px;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
        }

        .table img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .table th {
            color: #ffffff;
        }

        .btn-primary, .btn-danger, .btn-warning {
            padding: 10px 20px;
            border-radius: 5px;
        }

        .table-hover tbody tr:hover {
            background-color: #333;
        }

        .card-header {
            background-color: #333;
            color: #e0e0e0;
        }

        .pagination a {
            color: #e0e0e0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Shop Admin</h2>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="index.php">Trang Chủ</a></li>
            <li class="nav-item"><a class="nav-link" href="product.php">Quản lý sản phẩm</a></li>
            <li class="nav-item"><a class="nav-link" href="order.php">Quản lý đơn hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="customers.php">Quản lý khách hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="role.php">Cấp Quyền</a></li>
            <li class="nav-item"><a class="nav-link" href="supplier.php">Nhà cung cấp</a></li>
            <li class="nav-item"><a class="nav-link" href="change_price.php">Đổi giá trong ngày</a></li>
        </ul>
    </div>

    <div class="content">
        <h1 class="mt-4">Quản trị Admin</h1>

        <a href="add_product.php" class="btn btn-primary btn-lg mb-4">Thêm sản phẩm mới</a>

        <h2>Danh sách sản phẩm</h2>

        <!-- Form tìm kiếm và sắp xếp -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm sản phẩm" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-secondary" type="submit">Tìm kiếm</button>
            </div>
            <div class="mt-2">
                <label for="sort">Sắp xếp theo:</label>
                <select name="sort" id="sort" class="form-select d-inline w-auto">
                    <option value="asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                    <option value="desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                    <option value="id_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'id_asc' ? 'selected' : ''; ?>>ID tăng dần</option>
                    <option value="id_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'id_desc' ? 'selected' : ''; ?>>ID giảm dần</option>
                </select>
            </div>
        </form>

        <div class="card">
            <div class="card-header">
                Danh sách các sản phẩm
            </div>
            <div class="card-body">
                <table class="table table-dark table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng tổng</th>
                            <th>Cấu hình - số lượng từng cấu hình</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                    <td><img src="<?php echo $row['path']; ?>" alt="Product Image"></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VND</td>
                                    <td><?php echo number_format($row['total_quantity'], 0, ',', '.'); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['configurations'])); ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-warning btn-sm btn-action">Sửa</a>
                                        <a href="delete_product.php?id=<?php echo $row['product_id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" class="btn btn-danger btn-sm btn-action">Xóa</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Không có sản phẩm nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Phân trang -->
        <nav>
            <ul class="pagination">
                <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&sort=<?php echo htmlspecialchars($sort); ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&sort=<?php echo htmlspecialchars($sort); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page == $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&sort=<?php echo htmlspecialchars($sort); ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</body>
</html>
