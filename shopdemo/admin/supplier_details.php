<?php
session_start();
include '../includes/db.php'; // Kết nối với cơ sở dữ liệu

// Lấy ID của nhà cung cấp
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Nhà cung cấp không hợp lệ.');
}

$supplier_id = intval($_GET['id']);

// Lấy thông tin nhà cung cấp
$supplierQuery = "SELECT id, name, description FROM supplier WHERE id = $supplier_id";
$supplierResult = $conn->query($supplierQuery);
if ($supplierResult->num_rows === 0) {
    die('Nhà cung cấp không tồn tại.');
}
$supplier = $supplierResult->fetch_assoc();

// Chỉnh sửa sản phẩm của nhà cung cấp
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $price = intval($_POST['price']);

    // Kiểm tra sản phẩm có thuộc nhà cung cấp này không
    $checkProductQuery = "SELECT rd.id 
                          FROM receipt r 
                          JOIN receipt_details rd ON r.id = rd.receipt_id 
                          WHERE r.supplier_id = $supplier_id AND rd.product_id = $product_id";
    $checkProductResult = $conn->query($checkProductQuery);

    if ($checkProductResult->num_rows > 0) {
        // Cập nhật sản phẩm
        $updateProductQuery = "UPDATE receipt_details 
                               SET quantity = $quantity, price = $price 
                               WHERE product_id = $product_id";
        if ($conn->query($updateProductQuery)) {
            echo '<p style="color:green;">Thông tin sản phẩm đã được cập nhật thành công.</p>';
        } else {
            echo '<p style="color:red;">Lỗi khi cập nhật thông tin sản phẩm: ' . $conn->error . '</p>';
        }
    } else {
        echo '<p style="color:red;">Sản phẩm không tồn tại trong nhà cung cấp này.</p>';
    }
}

// Lấy danh sách sản phẩm của nhà cung cấp
$productQuery = "SELECT p.id, p.name, rd.quantity, rd.price
                  FROM receipt r
                  JOIN receipt_details rd ON r.id = rd.receipt_id
                  JOIN product p ON rd.product_id = p.id
                  WHERE r.supplier_id = $supplier_id";
$productResult = $conn->query($productQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Nhà Cung Cấp</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; /* Dark background */
            color: #e0e0e0; /* Light text color */
            margin: 0;
            padding: 0;
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #1f1f1f; /* Dark sidebar background */
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
        }

        .sidebar h2 {
            color: #f1f1f1; /* Lighter heading color */
            text-align: center;
            padding: 20px;
        }

        .sidebar .nav-link {
            color: #e0e0e0;
            padding: 10px;
            display: block;
            text-decoration: none;
        }

        .sidebar .nav-link:hover {
            background-color: #333;
            border-radius: 5px;
        }

        /* Content area */
        .content {
            margin-left: 250px;
            padding: 30px;
            background-color: #181818; /* Dark content background */
            color: #e0e0e0;
        }

        h2, h3 {
            color: #f1f1f1; /* Lighter text for headings */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #444; /* Darker borders */
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

        /* Link styling */
        a {
            color: #4CAF50; /* Light green link color */
        }

        a:hover {
            text-decoration: underline;
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
        <h2>Chi tiết Nhà Cung Cấp: <?php echo htmlspecialchars($supplier['name']); ?></h2>
        <p><?php echo htmlspecialchars($supplier['description']); ?></p>

        <h3>Sản phẩm của nhà cung cấp</h3>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá nhập</th>
                <th>Hành động</th>
            </tr>
            <?php
            if ($productResult->num_rows > 0) {
                while ($row = $productResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
                    echo '<td>' . number_format($row['price'], 0, ',', '.') . ' VND</td>';
                    echo '<td>
                            <form method="POST" action="supplier_details.php?id=' . $supplier_id . '">
                                <input type="hidden" name="product_id" value="' . $row['id'] . '">
                                <input type="number" name="quantity" value="' . htmlspecialchars($row['quantity']) . '" min="1" required>
                                <input type="number" name="price" value="' . htmlspecialchars($row['price']) . '" min="1" required>
                                <button type="submit" name="edit_product">Chỉnh sửa</button>
                            </form>
                          </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="5">Không có sản phẩm nào được liên kết với nhà cung cấp này</td></tr>';
            }
            ?>
        </table>

        
    </div>
</body>
</html>

<?php
$conn->close();
?>

