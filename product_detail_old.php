<?php
session_start();

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Kết nối cơ sở dữ liệu
$conn = new mysqli('localhost', 'root', '', 'shop');

// Kiểm tra kết nối
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Lấy thông tin sản phẩm
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    echo "Sản phẩm không tồn tại!";
    exit();
}

// Truy vấn chi tiết sản phẩm và phân biệt ảnh chính và ảnh minh hoạ
$sql = "
    SELECT p.name AS product_name, p.description AS product_description, 
           pr.price AS product_price, pr.temporary_price, pr.discount_start, pr.discount_end,
           u.name AS unit_name, img.path AS product_image, img.sort_order,
           (SELECT SUM(rd.quantity) FROM receipt_details rd WHERE rd.product_id = p.id) AS stock_quantity
    FROM product p
    JOIN price pr ON p.id = pr.product_id
    JOIN unit u ON p.unit_id = u.id
    LEFT JOIN image img ON p.id = img.product_id
    WHERE p.id = $product_id
    ORDER BY img.sort_order ASC
";
$result = $conn->query($sql);

// Biến lưu thông tin sản phẩm
$product = null;
$product_images = [];
$main_image = ''; // Biến lưu ảnh chính (sort_order = 0)

// Lưu thông tin sản phẩm
while ($row = $result->fetch_assoc()) {
    if (!$product) {
        $product = $row;
    }
    if ($row['sort_order'] == 1) {
        $main_image = $row['product_image'];
    } else {
        $product_images[] = $row;
    }
}

// Kiểm tra xem sản phẩm có tồn tại
if (!$product) {
    echo "Sản phẩm không tồn tại!";
    exit();
}

// Kiểm tra thời gian khuyến mãi
$current_time = date('Y-m-d H:i:s');
$is_on_sale = false;
if ($current_time >= $product['discount_start'] && $current_time <= $product['discount_end']) {
    $is_on_sale = true;
    $price = $product['temporary_price'];  // Lấy giá khuyến mãi
} else {
    $price = $product['product_price'];  // Giá gốc
}

// Lấy thông tin cấu hình của sản phẩm
$config_sql = "
    SELECT cc.color_name, cc.configuration_name, cc.quantity 
    FROM colors_configuration cc
    WHERE cc.product_id = $product_id
";
$config_result = $conn->query($config_sql);
?>
<!-- Bao gồm header từ thư mục includes -->
<?php include('includes/header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* CSS tùy chỉnh cho các phần */
        .thumbnail {
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 5px;
            margin-top: 5px;
        }

        .thumbnail:hover {
            border: 1px solid #000;
        }

        .product-gallery {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .product-gallery img {
            max-width: 100%;
            max-height: 400px;
        }

        .image-thumbnails {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .image-thumbnails img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin: 0 10px;
        }

        .sale-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .comments-section {
            margin-top: 30px;
        }

        .comment {
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .comment-author {
            font-weight: bold;
        }

        .comment-date {
            font-size: 0.9em;
            color: #888;
        }

        .comment-content {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
        <div class="row">
            <div class="col-md-6 product-gallery">
                <div style="position: relative;">
                    <?php if ($is_on_sale): ?>
                        <div class="sale-badge">Sale</div>
                    <?php endif; ?>
                    <img id="main-image" src="admin/<?php echo htmlspecialchars($main_image); ?>" alt="Image of <?php echo htmlspecialchars($product['product_name']); ?>" class="img-fluid">
                </div>
            </div>
            <div class="col-md-6">
                <h3>Giá: <?php echo number_format($price, 0, ',', '.'); ?> VND</h3>
                <p><strong>Đơn vị:</strong> <?php echo htmlspecialchars($product['unit_name']); ?></p>
                <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($product['product_description'])); ?></p>
                <p><strong>Số lượng tồn kho:</strong> <?php echo $product['stock_quantity'] ? $product['stock_quantity'] : 'Không có'; ?></p>

                <!-- Form chọn số lượng và cấu hình -->
<form method="POST" action="add_to_cart.php">
    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
    <div class="mb-3">
        <label for="quantity" class="form-label">Số lượng</label>
        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required>
    </div>

    <!-- Lựa chọn cấu hình -->
    <div class="mb-3">
        <label for="configuration" class="form-label">Chọn cấu hình</label>
        <select name="configuration" id="configuration" class="form-control" required>
            <option value="">Chọn cấu hình</option>
            <?php
            if ($config_result->num_rows > 0) {
                while ($config = $config_result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($config['color_name']) . " - " . htmlspecialchars($config['configuration_name']) . "'>";
                    echo htmlspecialchars($config['color_name']) . " - " . htmlspecialchars($config['configuration_name']);
                    echo "</option>";
                }
            } else {
                echo "<option value=''>Không có cấu hình nào</option>";
            }
            ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Thêm vào giỏ hàng</button>
</form>
            </div>
        </div>

        <div class="image-thumbnails">
            <?php
            if (count($product_images) > 0):
                foreach ($product_images as $image):
                    if (isset($image['product_image']) && !empty($image['product_image'])):
            ?>
                        <img src="admin/<?php echo htmlspecialchars($image['product_image']); ?>" class="thumbnail" onclick="changeImage('<?php echo 'admin/' . htmlspecialchars($image['product_image']); ?>')">
            <?php
                    endif;
                endforeach;
            else:
                echo "<p>Không có ảnh minh hoạ.</p>";
            endif;
            ?>
        </div>

        <div class="comments-section">
            <h4>Bình luận</h4>
            <?php
            $comment_sql = "
                SELECT c.content AS comment_content, c.created_at AS comment_date, 
                       CONCAT(u.firstname, ' ', u.familyname) AS user_name
                FROM comment c
                JOIN user u ON c.user_id = u.id
                WHERE c.product_id = $product_id
                ORDER BY c.created_at DESC
            ";
            $comments_result = $conn->query($comment_sql);
            if ($comments_result->num_rows > 0) {
                while ($comment = $comments_result->fetch_assoc()) {
            ?>
                    <div class="comment">
                        <div class="comment-author"><?php echo htmlspecialchars($comment['user_name']); ?></div>
                        <div class="comment-date"><?php echo htmlspecialchars($comment['comment_date']); ?></div>
                        <div class="comment-content"><?php echo nl2br(htmlspecialchars($comment['comment_content'])); ?></div>
                    </div>
            <?php
                }
            } else {
                echo "<p>Chưa có bình luận nào.</p>";
            }
            ?>

            <!-- Form bình luận -->
            <h5>Thêm bình luận</h5>
            <form method="POST" action="add_comment.php">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <div class="mb-3">
                    <label for="comment" class="form-label">Nội dung bình luận</label>
                    <textarea name="comment" id="comment" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Gửi bình luận</button>
            </form>
        </div>

        <br>
        <a href="index.php" class="btn btn-secondary">Trở lại trang chủ</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script>
        // JavaScript to change the main image when a thumbnail is clicked
        function changeImage(imageSrc) {
            document.getElementById('main-image').src = imageSrc;
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
