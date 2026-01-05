<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "shop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Kiểm tra xem người dùng có quyền admin không
$sql_check_admin = "SELECT r.name FROM role r 
                    JOIN user_role ur ON ur.role_id = r.id 
                    WHERE ur.user_id = '$user_id' AND r.name = 'admin'";
$result_check_admin = $conn->query($sql_check_admin);

if ($result_check_admin->num_rows == 0) {
    echo "Bạn không có quyền truy cập trang này.";
    exit();
}

// Lấy danh sách tất cả người dùng cùng với quyền của họ
$sql_users = "SELECT u.id, u.username, u.email, r.name AS role_name 
              FROM user u 
              LEFT JOIN user_role ur ON u.id = ur.user_id
              LEFT JOIN role r ON ur.role_id = r.id";
$result_users = $conn->query($sql_users);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/role.css">
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
        <h2 class="mt-4">Danh sách người dùng và quyền</h2>

        <!-- Table hiển thị danh sách người dùng và quyền -->
        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Quyền</th>
                    <th>Chỉnh sửa quyền</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_users->num_rows > 0) {
                    while ($row = $result_users->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td>" . htmlspecialchars($row['role_name']) . "</td>
                                <td>
                                    <form method='POST' action='edit_role.php'>
                                        <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                                        <select name='role_id'>";
                        
                        // Lấy danh sách quyền từ bảng role để chọn
                        $sql_roles = "SELECT * FROM role";
                        $result_roles = $conn->query($sql_roles);
                        while ($role = $result_roles->fetch_assoc()) {
                            echo "<option value='" . $role['id'] . "' " . ($role['name'] == $row['role_name'] ? 'selected' : '') . ">" . $role['name'] . "</option>";
                        }
                        
                        echo "</select>
                              <button type='submit'>Cập nhật quyền</button>
                            </form>
                        </td>
                      </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Không có người dùng nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Nút quay lại -->
        <a href="index.php" class="btn btn-secondary">Quay lại trang chủ</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
