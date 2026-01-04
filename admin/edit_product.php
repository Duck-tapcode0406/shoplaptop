<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

$conn = new mysqli('localhost', 'root', '', 'shop');
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Thiếu ID sản phẩm.";
    exit();
}

$product_id = intval($_GET['id']);

// Lấy thông tin sản phẩm chính
$sql = "SELECT p.id AS product_id, p.name, p.description, p.unit_id, 
               pr.price AS sell_price, pr.temporary_price, pr.discount_start, pr.discount_end,
               u.name AS unit_name, 
               s.id AS supplier_id, s.name AS supplier_name, r.datetime AS entry_date,
               rd.price AS purchase_price, rd.quantity AS purchase_quantity,
               GROUP_CONCAT(i.path ORDER BY i.sort_order) AS image_paths,
               c.color_name, c.configuration_name
        FROM product p
        LEFT JOIN price pr ON p.id = pr.product_id
        LEFT JOIN unit u ON p.unit_id = u.id
        LEFT JOIN receipt_details rd ON p.id = rd.product_id
        LEFT JOIN receipt r ON rd.receipt_id = r.id
        LEFT JOIN supplier s ON r.supplier_id = s.id
        LEFT JOIN image i ON p.id = i.product_id
        LEFT JOIN colors_configuration c ON p.id = c.product_id
        WHERE p.id = ?
        GROUP BY p.id, pr.price, u.name, s.id, r.datetime, rd.price, rd.quantity";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Không tìm thấy sản phẩm.";
    exit();
}

// Lấy tất cả màu sắc và cấu hình của sản phẩm
$sql_colors = "SELECT * FROM colors_configuration WHERE product_id = ?";
$stmt = $conn->prepare($sql_colors);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$colors_config = $stmt->get_result();

// Lấy tất cả ảnh của sản phẩm
$sql_images = "SELECT * FROM image WHERE product_id = ? ORDER BY sort_order";
$stmt = $conn->prepare($sql_images);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$images_result = $stmt->get_result();

// Lấy tất cả các đơn vị
$unit_result = $conn->query("SELECT * FROM unit");
$units = $unit_result->fetch_all(MYSQLI_ASSOC);

// Lấy tất cả các nhà cung cấp
$supplier_result = $conn->query("SELECT * FROM supplier");
$suppliers = $supplier_result->fetch_all(MYSQLI_ASSOC);

// Xử lý cập nhật sản phẩm khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $sell_price = $_POST['sell_price'];
    $purchase_price = $_POST['purchase_price'];
    $unit_id = $_POST['unit_id'];
    $supplier_id = $_POST['supplier_id'];
    $entry_date = $_POST['entry_date'];

    // Cập nhật thông tin sản phẩm vào cơ sở dữ liệu
    $update_product_sql = "UPDATE product SET name = ?, description = ?, unit_id = ? WHERE id = ?";
    $stmt = $conn->prepare($update_product_sql);
    $stmt->bind_param('ssii', $name, $description, $unit_id, $product_id);
    $stmt->execute();

    // Cập nhật giá bán và khuyến mãi
    $update_price_sql = "UPDATE price SET price = ?, temporary_price = ? WHERE product_id = ?";
    $stmt = $conn->prepare($update_price_sql);
    $stmt->bind_param('dii', $sell_price, $purchase_price, $product_id);
    $stmt->execute();

    // Cập nhật ngày nhập
    $update_receipt_sql = "UPDATE receipt SET datetime = ? WHERE supplier_id = ? AND id IN (SELECT receipt_id FROM receipt_details WHERE product_id = ?)";
    $stmt = $conn->prepare($update_receipt_sql);
    $stmt->bind_param('sii', $entry_date, $supplier_id, $product_id);
    $stmt->execute();

    // Cập nhật màu sắc và cấu hình sản phẩm
    if (isset($_POST['color_name']) && isset($_POST['configuration_name']) && isset($_POST['color_id'])) {
        foreach ($_POST['color_id'] as $index => $color_id) {
            $color_name = $_POST['color_name'][$index];
            $configuration_name = $_POST['configuration_name'][$index];
            $quantity = $_POST['quantity'][$index];

            // Cập nhật thông tin màu sắc và cấu hình dựa trên ID
            $update_color_sql = "UPDATE colors_configuration 
                                 SET color_name = ?, configuration_name = ?, quantity = ? 
                                 WHERE id = ? AND product_id = ?";
            $stmt = $conn->prepare($update_color_sql);
            $stmt->bind_param('ssiii', $color_name, $configuration_name, $quantity, $color_id, $product_id);
            $stmt->execute();
        }
    }

    // Cập nhật ảnh sản phẩm
    if (isset($_FILES['image']) && $_FILES['image']['error'][0] != 4) {
        foreach ($_FILES['image']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['image']['name'][$key];
            $file_path = 'uploads/' . $file_name;

            if (move_uploaded_file($tmp_name, $file_path)) {
                // Lưu ảnh vào cơ sở dữ liệu
                $insert_image_sql = "INSERT INTO image (product_id, path) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_image_sql);
                $stmt->bind_param('is', $product_id, $file_path);
                $stmt->execute();
            }
        }
    }

    // Chuyển hướng về trang product.php sau khi cập nhật thành công
    header("Location: product.php");
    exit();
}
?>

<!-- Form HTML -->
<form method="POST" enctype="multipart/form-data">
    <h2>Cập nhật sản phẩm: <?php echo htmlspecialchars($product['name']); ?></h2>
    
    <div class="form-group">
        <label for="name">Tên sản phẩm</label>
        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Mô tả</label>
        <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="sell_price">Giá bán</label>
        <input type="number" name="sell_price" id="sell_price" class="form-control" value="<?php echo htmlspecialchars($product['sell_price']); ?>" required>
    </div>

    <div class="form-group">
        <label for="purchase_price">Giá nhập</label>
        <input type="number" name="purchase_price" id="purchase_price" class="form-control" value="<?php echo htmlspecialchars($product['purchase_price']); ?>" required>
    </div>

    <div class="form-group">
        <label for="unit_id">Đơn vị</label>
        <select name="unit_id" id="unit_id" class="form-control" required>
            <?php foreach ($units as $unit): ?>
                <option value="<?php echo $unit['id']; ?>" <?php echo $product['unit_id'] == $unit['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($unit['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="supplier_id">Nhà cung cấp</label>
        <select name="supplier_id" id="supplier_id" class="form-control" required>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?php echo $supplier['id']; ?>" <?php echo $product['supplier_id'] == $supplier['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($supplier['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="entry_date">Ngày nhập</label>
        <input type="date" name="entry_date" id="entry_date" class="form-control" value="<?php echo htmlspecialchars($product['entry_date']); ?>" required>
    </div>

    <h3>Màu sắc và cấu hình</h3>
    <?php while ($color_config = $colors_config->fetch_assoc()): ?>
        <div class="form-group">
            <label for="color_name">Tên màu</label>
            <input type="text" name="color_name[]" class="form-control" value="<?php echo htmlspecialchars($color_config['color_name']); ?>" required>
            <input type="hidden" name="color_id[]" value="<?php echo $color_config['id']; ?>"> <!-- ID màu sắc -->
        </div>

        <div class="form-group">
            <label for="configuration_name">Cấu hình</label>
            <input type="text" name="configuration_name[]" class="form-control" value="<?php echo htmlspecialchars($color_config['configuration_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="quantity">Số lượng</label>
            <input type="number" name="quantity[]" class="form-control" value="<?php echo htmlspecialchars($color_config['quantity']); ?>" required>
        </div>
    <?php endwhile; ?>

    <h3>Ảnh sản phẩm</h3>
    <div class="form-group">
        <input type="file" name="image[]" class="form-control" multiple>
    </div>

    <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
</form>
