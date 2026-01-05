<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $unit_id = $_POST['unit_id'];
    $sell_price = $_POST['sell_price'];
    $purchase_price = $_POST['purchase_price'];
    $supplier_id = $_POST['supplier_id'];
    $entry_date = $_POST['entry_date'];

    // Kết nối cơ sở dữ liệu
    $conn = new mysqli('localhost', 'root', '', 'shop');
    if ($conn->connect_error) {
        die('Kết nối thất bại: ' . $conn->connect_error);
    }

    // Thêm sản phẩm vào bảng 'product'
    $sql_product = "INSERT INTO product (name, description, unit_id) 
                    VALUES ('$name', '$description', '$unit_id')";
    
    if ($conn->query($sql_product) === TRUE) {
        $product_id = $conn->insert_id;

        // Thêm giá bán vào bảng 'price'
        $sql_price = "INSERT INTO price (product_id, price, datetime) 
                      VALUES ('$product_id', '$sell_price', NOW())";
        
        if ($conn->query($sql_price) === TRUE) {
            // Tạo phiếu nhập mới
            $sql_receipt = "INSERT INTO receipt (supplier_id, datetime) 
                            VALUES ('$supplier_id', '$entry_date')";
            if ($conn->query($sql_receipt) === TRUE) {
                $receipt_id = $conn->insert_id;

                // Lưu thông tin màu và cấu hình vào bảng 'colors_configuration'
                $total_quantity = 0;
                $color_names = $_POST['color_name'];
                $configuration_names = $_POST['configuration_name'];
                $quantities = $_POST['quantity'];
                for ($i = 0; $i < count($color_names); $i++) {
                    $color_name = $color_names[$i];
                    $configuration_name = $configuration_names[$i];
                    $quantity = $quantities[$i];
                    $total_quantity += $quantity;

                    $sql_colors_config = "INSERT INTO colors_configuration (product_id, color_name, configuration_name, quantity) 
                                          VALUES ('$product_id', '$color_name', '$configuration_name', '$quantity')";
                    $conn->query($sql_colors_config);
                }

                // Cập nhật số lượng vào bảng 'receipt_details'
                $sql_receipt_details = "INSERT INTO receipt_details (receipt_id, product_id, price, quantity) 
                                        VALUES ('$receipt_id', '$product_id', '$purchase_price', '$total_quantity')";
                if ($conn->query($sql_receipt_details) === TRUE) {
                    // Xử lý ảnh
                    if (isset($_FILES['image']) && $_FILES['image']['error'][0] == 0) {
                        $sort_order = 1;
                        $target_dir = "uploads/";
                        foreach ($_FILES['image']['name'] as $key => $image_name) {
                            $target_file = $target_dir . basename($image_name);
                            if (move_uploaded_file($_FILES['image']['tmp_name'][$key], $target_file)) {
                                // Thêm ảnh vào bảng 'image'
                                $sql_image = "INSERT INTO image (product_id, path, sort_order) 
                                              VALUES ('$product_id', '$target_file', '$sort_order')";
                                if ($conn->query($sql_image) !== TRUE) {
                                    echo "Lỗi tải ảnh: " . $conn->error;
                                }
                                $sort_order++; 
                            }
                        }
                    }
                    echo "Sản phẩm, cấu hình, phiếu nhập và ảnh đã được thêm thành công.";
                } else {
                    echo "Lỗi thêm chi tiết phiếu nhập: " . $conn->error;
                }
            } else {
                echo "Lỗi tạo phiếu nhập: " . $conn->error;
            }
        } else {
            echo "Lỗi thêm giá bán: " . $conn->error;
        }
    } else {
        echo "Lỗi thêm sản phẩm: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm mới</title>
</head>
<body>
    <h1>Thêm sản phẩm mới</h1>
    <form method="POST" enctype="multipart/form-data">
        <!-- Các trường nhập liệu sản phẩm -->
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" required></textarea><br><br>

        <label for="unit_id">Đơn vị:</label>
        <select id="unit_id" name="unit_id" required>
            <?php
            $conn = new mysqli('localhost', 'root', '', 'shop');
            $sql = "SELECT * FROM unit";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
            }
            $conn->close();
            ?>
        </select><br><br>

        <label for="sell_price">Giá bán:</label>
        <input type="number" id="sell_price" name="sell_price" required><br><br>

        <label for="purchase_price">Giá nhập:</label>
        <input type="number" id="purchase_price" name="purchase_price" required><br><br>

        <label for="supplier_id">Nhà cung cấp:</label>
        <select id="supplier_id" name="supplier_id" required>
            <?php
            $conn = new mysqli('localhost', 'root', '', 'shop');
            $sql = "SELECT id, name FROM supplier";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
            }
            $conn->close();
            ?>
        </select><br><br>

        <label for="entry_date">Ngày nhập:</label>
        <input type="date" id="entry_date" name="entry_date" required><br><br>

        <!-- Các trường nhập liệu cho cấu hình màu và số lượng -->
        <div id="configurations">
            <div class="configuration">
                <label for="color_name[]">Màu sắc:</label>
                <input type="text" name="color_name[]" required><br><br>

                <label for="configuration_name[]">Cấu hình:</label>
                <input type="text" name="configuration_name[]" required><br><br>

                <label for="quantity[]">Số lượng:</label>
                <input type="number" name="quantity[]" required><br><br>
            </div>
        </div>

        <button type="button" id="addConfig">Thêm cấu hình</button><br><br>

        <label for="images">Ảnh sản phẩm:</label>
        <input type="file" id="images" name="image[]" accept="image/*" multiple><br><br>

        <button type="submit">Thêm sản phẩm</button>
    </form>

    <script>
    document.getElementById('addConfig').addEventListener('click', function() {
    var newConfig = document.createElement('div');
    newConfig.classList.add('configuration');
    newConfig.innerHTML = `
        <label for="color_name[]">Màu sắc:</label>
        <input type="text" name="color_name[]" required><br><br>

        <label for="configuration_name[]">Cấu hình:</label>
        <input type="text" name="configuration_name[]" required><br><br>

        <label for="quantity[]">Số lượng:</label>
        <input type="number" name="quantity[]" required><br><br>
    `;
    document.getElementById('configurations').appendChild(newConfig);
});
    </script>

    <p><a href="product.php">Quay lại danh sách sản phẩm</a></p>
</body>
</html>