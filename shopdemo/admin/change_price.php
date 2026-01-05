<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "shop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Lấy danh sách sản phẩm để hiển thị trong dropdown
$result = $conn->query("SELECT id, name FROM product");
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Kiểm tra nếu form đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $product_id = $_POST['product_id'];
    $discount_percentage = $_POST['discount_percentage'];
    $temporary_price = $_POST['temporary_price'];

    // Lấy giá gốc từ bảng price
    $price_result = $conn->query("SELECT price FROM price WHERE product_id = $product_id LIMIT 1");
    $price_row = $price_result->fetch_assoc();
    $price = $price_row['price'];

    // Tính toán giá tạm thời hoặc phần trăm giảm giá
    if ($temporary_price !== "") {
        $discount_percentage = (($price - $temporary_price) / $price) * 100;
    } else {
        $temporary_price = $price - ($price * $discount_percentage / 100);
    }

    // Tính toán ngày bắt đầu và kết thúc giảm giá
    $discount_start = null;
    $discount_end = null;

    $hours = $_POST['hours'];
    $days = $_POST['days'];
    $months = $_POST['months'];

    if ($hours || $days || $months) {
        $discount_start = date("Y-m-d H:i:s");

        // Tính toán thời gian kết thúc giảm giá dựa trên giờ, ngày, hoặc tháng
        if ($hours) {
            $discount_end = date("Y-m-d H:i:s", strtotime("+$hours hours", strtotime($discount_start)));
        } elseif ($days) {
            $discount_end = date("Y-m-d H:i:s", strtotime("+$days days", strtotime($discount_start)));
        } elseif ($months) {
            $discount_end = date("Y-m-d H:i:s", strtotime("+$months months", strtotime($discount_start)));
        }
    }

    // Cập nhật giá và giảm giá vào bảng price
    $sql = "UPDATE price 
            SET temporary_price = ?, discount_start = ?, discount_end = ? 
            WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssi", $temporary_price, $discount_start, $discount_end, $product_id);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Cập nhật giá thành công!</p>";
    } else {
        echo "<p style='color:red;'>Lỗi: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Lấy danh sách các sản phẩm có giá tạm thời khác NULL
$discount_query = "SELECT p.id, p.name, pr.price, pr.temporary_price, pr.discount_start, pr.discount_end
                   FROM product p
                   JOIN price pr ON p.id = pr.product_id
                   WHERE pr.temporary_price IS NOT NULL";
$discount_result = $conn->query($discount_query);
$discount_products = [];
while ($row = $discount_result->fetch_assoc()) {
    $discount_products[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa giảm giá sản phẩm</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Assuming you have a common CSS file -->
    <style>
        /* Your existing custom CSS */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 220px;
            background-color: #333;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100%;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 24px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            padding: 10px;
            text-align: center;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
        }
        .sidebar ul li a:hover {
            background-color: #575757;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
            background-color: #f4f4f4;
            flex: 1;
        }
        h1 {
            color: #333;
        }
        label, select, input {
            margin: 10px 0;
            padding: 8px;
            font-size: 14px;
        }
        input[type="number"], input[type="datetime-local"] {
            width: 200px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Shop Admin</h2>
        <ul class="nav flex-column">
            <li><a href="index.php">Trang Chủ</a></li>
            <li><a href="product.php">Quản lý sản phẩm</a></li>
            <li><a href="order.php">Quản lý đơn hàng</a></li>
            <li><a href="customers.php">Quản lý khách hàng</a></li>
            <li><a href="role.php">Cấp Quyền</a></li>
            <li><a href="supplier.php">Nhà cung cấp</a></li>
            <li><a href="change_price.php">Đổi giá trong ngày</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h1>Chỉnh sửa giảm giá sản phẩm</h1>
        <form action="change_price.php" method="POST">
            <label for="product_id">Chọn sản phẩm:</label>
            <select name="product_id" id="product_id" required>
                <?php
                // Hiển thị danh sách sản phẩm
                foreach ($products as $product) {
                    echo "<option value='{$product['id']}'>{$product['name']}</option>";
                }
                ?>
            </select>
            <br>

            <label for="price">Giá gốc:</label>
            <input type="number" id="price" name="price" readonly>
            <br>

            <label for="temporary_price">Giá tạm thời:</label>
            <input type="number" id="temporary_price" name="temporary_price" step="0.01">
            <br>

            <label for="discount_percentage">Phần trăm giảm giá (%):</label>
            <input type="number" id="discount_percentage" name="discount_percentage" min="0" max="100">
            <br>

            <label for="hours">Số giờ giảm giá:</label>
            <input type="number" id="hours" name="hours" min="0">
            <br>

            <label for="days">Số ngày giảm giá:</label>
            <input type="number" id="days" name="days" min="0">
            <br>

            <label for="months">Số tháng giảm giá:</label>
            <input type="number" id="months" name="months" min="0">
            <br>

            <button type="submit">Cập nhật giá</button>
        </form>

        <h2>Sản phẩm có giảm giá</h2>
        <table>
            <thead>
                <tr>
                    <th>Tên sản phẩm</th>
                    <th>Giá gốc</th>
                    <th>Giá tạm thời</th>
                    <th>Ngày bắt đầu giảm giá</th>
                    <th>Ngày kết thúc giảm giá</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($discount_products as $product) { ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($product['temporary_price'], 0, ',', '.'); ?></td>
                        <td><?php echo $product['discount_start']; ?></td>
                        <td><?php echo $product['discount_end']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <script>
            // Lấy giá gốc từ bảng price
            document.getElementById('product_id').addEventListener('change', function() {
                var productId = this.value;
                fetch(`get_price.php?product_id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('price').value = data.price;
                        document.getElementById('temporary_price').value = data.price;
                        document.getElementById('discount_percentage').value = 0;
                    });
            });

            // Cập nhật giá tạm thời khi nhập phần trăm giảm giá
            document.getElementById('discount_percentage').addEventListener('input', function() {
                var price = parseFloat(document.getElementById('price').value);
                var discountPercentage = parseFloat(this.value);
                if (!isNaN(price) && !isNaN(discountPercentage)) {
                    var tempPrice = price - (price * discountPercentage / 100);
                    document.getElementById('temporary_price').value = tempPrice.toFixed(2);
                }
            });

            // Cập nhật phần trăm giảm giá khi nhập giá tạm thời
            document.getElementById('temporary_price').addEventListener('input', function() {
                var price = parseFloat(document.getElementById('price').value);
                var tempPrice = parseFloat(this.value);
                if (!isNaN(price) && !isNaN(tempPrice)) {
                    var discountPercentage = ((price - tempPrice) / price) * 100;
                    document.getElementById('discount_percentage').value = discountPercentage.toFixed(2);
                }
            });
        </script>
    </div>
</body>
</html>
