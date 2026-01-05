<?php
session_start();
include '../includes/db.php';  // Kết nối với cơ sở dữ liệu

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kiểm tra quyền admin
$user_id = $_SESSION['user_id'];
$sql_check_admin = "SELECT r.name FROM role r 
                    JOIN user_role ur ON ur.role_id = r.id 
                    WHERE ur.user_id = '$user_id' AND r.name = 'admin'";
$result_check_admin = $conn->query($sql_check_admin);

if ($result_check_admin->num_rows == 0) {
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

// Xử lý việc lọc nhà cung cấp
$filter = '';
if (isset($_GET['filter'])) {
    $filter = $conn->real_escape_string($_GET['filter']);
}

// Xử lý phân trang
$limit = 10;  // Số nhà cung cấp mỗi trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Lấy danh sách nhà cung cấp với phân trang và lọc
$query = "SELECT id, name, description FROM supplier 
          WHERE name LIKE '%$filter%' 
          ORDER BY name ASC 
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Lấy danh sách tất cả nhà cung cấp để tạo danh sách lọc
$query_all_suppliers = "SELECT DISTINCT name FROM supplier ORDER BY name ASC";
$result_all_suppliers = $conn->query($query_all_suppliers);

// Tính toán tổng số trang
$totalQuery = "SELECT COUNT(*) AS total FROM supplier WHERE name LIKE '%$filter%'";
$totalResult = $conn->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà Cung Cấp</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }

        .content {
            margin: 30px auto;
            padding: 20px;
            max-width: 1000px;
            background-color: #181818;
            color: #e0e0e0;
        }

        h2, h3 {
            color: #f1f1f1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #444;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 16px;
            margin: 0 5px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #007bff;
        }

        .back-link {
            margin-top: 20px;
            display: block;
            color: #4CAF50;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: #181818;
            color: #fff;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
            color: #f1f1f1;
        }

        .sidebar .nav {
            list-style-type: none;
            padding: 0;
        }

        .sidebar .nav-item {
            padding: 15px;
            text-align: center;
        }

        .sidebar .nav-item a {
            color: #e0e0e0;
            text-decoration: none;
        }

        .sidebar .nav-item a:hover {
            background-color: #333;
        }

        .content {
            margin-left: 220px; /* Sidebar width */
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
        <h2>Quản lý Nhà Cung Cấp</h2>

        <!-- Nút thêm nhà cung cấp -->
        <a href="add_supplier.php">
            <button>Thêm Nhà Cung Cấp</button>
        </a>

        <!-- Lọc nhà cung cấp -->
        <form method="GET" action="supplier.php">
            <select name="filter" onchange="this.form.submit()">
                <option value="">-- Tất cả Nhà Cung Cấp --</option>
                <?php
                if ($result_all_suppliers->num_rows > 0) {
                    while ($row = $result_all_suppliers->fetch_assoc()) {
                        $selected = ($row['name'] == $filter) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['name']) . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                    }
                }
                ?>
            </select>
        </form>

        <!-- Danh Sách Nhà Cung Cấp -->
        <h3>Danh Sách Nhà Cung Cấp</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Mô tả</th>
                <th>Hành động</th>
                <th>Chi Tiết</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['description']) . '</td>';
                    echo '<td>';
                    echo '<form method="POST" action="delete_supplier.php" style="display:inline;">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                    echo '<button type="submit">Xóa</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '<td>';
                    // Thêm liên kết để xem chi tiết nhà cung cấp
                    echo '<a href="supplier_details.php?id=' . htmlspecialchars($row['id']) . '">Xem Chi Tiết</a>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">Không có nhà cung cấp nào</td></tr>';
            }
            ?>
        </table>

        <!-- Phân trang -->
        <div class="pagination">
            <?php
            if ($page > 1) {
                echo '<a href="supplier.php?page=' . ($page - 1) . '&filter=' . htmlspecialchars($filter) . '">« Trước</a>';
            }
            for ($i = 1; $i <= $totalPages; $i++) {
                echo '<a href="supplier.php?page=' . $i . '&filter=' . htmlspecialchars($filter) . '">' . $i . '</a>';
            }
            if ($page < $totalPages) {
                echo '<a href="supplier.php?page=' . ($page + 1) . '&filter=' . htmlspecialchars($filter) . '">Tiếp theo »</a>';
            }
            ?>
        </div>

        <a href="index.php" class="back-link">Quay lại trang chủ</a>
    </div>

</body>
</html>

<?php
// Đóng kết nối cơ sở dữ liệu
$conn->close();
?>
