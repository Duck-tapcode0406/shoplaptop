<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Lấy giá trị tìm kiếm từ form
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Số lượng người dùng mỗi trang
$limit = 10;

// Lấy số trang hiện tại, mặc định là trang 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Truy vấn để lấy số lượng người dùng
$sql_count = "SELECT COUNT(*) AS total FROM user u
              JOIN user_role ur ON u.id = ur.user_id
              JOIN role r ON ur.role_id = r.id
              WHERE r.name = 'customer' AND u.username LIKE '%$search%'";

$result_count = $conn->query($sql_count);
$total_rows = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Truy vấn để lấy thông tin người dùng có role là 'customer' và theo tên tìm kiếm
$sql = "SELECT u.id, u.username, u.password, u.email, u.phone
        FROM user u
        JOIN user_role ur ON u.id = ur.user_id
        JOIN role r ON ur.role_id = r.id
        WHERE r.name = 'customer' AND u.username LIKE '%$search%'
        LIMIT $offset, $limit";

// Thực hiện truy vấn
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách người dùng - Customer</title>
    <link rel="stylesheet" href="assets/css/customers.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Style cho header và sidebar */
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

        button {
            margin-top: 20px;
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

    <!-- Content -->
    <div class="content">
        <h1 class="mt-4">Danh sách người dùng có role là customer</h1>

        <!-- Form tìm kiếm -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm theo tên" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-outline-secondary" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <!-- Table hiển thị danh sách khách hàng -->
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-dark table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Password</th> <!-- Hiển thị mật khẩu -->
                        <th>Email</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['password']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Không có người dùng nào có role là customer.</p>
        <?php endif; ?>

        <!-- Phân trang -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <!-- Nút trở về -->
        <button onclick="window.location.href='index.php';" class="btn btn-secondary btn-sm">Trở về</button>
    </div>
</body>
</html>

<?php
$conn->close();
?>
