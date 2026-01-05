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

// Khởi tạo các biến tìm kiếm
$search_keyword = isset($_POST['search_keyword']) ? $_POST['search_keyword'] : '';
$min_price = isset($_POST['min_price']) ? $_POST['min_price'] : null;
$max_price = isset($_POST['max_price']) ? $_POST['max_price'] : null;

// Xây dựng phần điều kiện cho truy vấn SQL
$price_condition = "";
if ($min_price !== null && $max_price !== null) {
    $price_condition = "AND pr.price BETWEEN $min_price AND $max_price";
} elseif ($min_price !== null) {
    $price_condition = "AND pr.price >= $min_price";
} elseif ($max_price !== null) {
    $price_condition = "AND pr.price <= $max_price";
}

// Tính số lượng sản phẩm tổng cộng
$count_sql = "
    SELECT COUNT(*) AS total_products
    FROM 
        product p
    LEFT JOIN 
        price pr 
    ON 
        p.id = pr.product_id
    LEFT JOIN 
        image img 
    ON 
        p.id = img.product_id AND img.sort_order = 1  -- Lấy ảnh chính (sort_order = 1)
    WHERE 
        (p.name LIKE '%$search_keyword%' OR p.description LIKE '%$search_keyword%')
        $price_condition
";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total_products'];

// Xác định số sản phẩm mỗi trang và tổng số trang
$products_per_page = 4;
$total_pages = ceil($total_products / $products_per_page);

// Xác định trang hiện tại (mặc định là trang 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page > 0) ? $page : 1;  // Đảm bảo trang không âm

// Tính chỉ mục bắt đầu cho truy vấn SQL
$offset = ($page - 1) * $products_per_page;

// Truy vấn danh sách sản phẩm theo tìm kiếm (theo tên, mô tả và khoảng giá), chỉ lấy ảnh chính
$sql = "
    SELECT 
        p.id,
        p.name,
        p.description,
        pr.price,
        img.path
    FROM 
        product p
    LEFT JOIN 
        price pr 
    ON 
        p.id = pr.product_id
    LEFT JOIN 
        image img 
    ON 
        p.id = img.product_id AND img.sort_order = 1  -- Lấy ảnh chính (sort_order = 1)
    WHERE 
        (p.name LIKE '%$search_keyword%' OR p.description LIKE '%$search_keyword%')
        $price_condition
    LIMIT $offset, $products_per_page
";

if ($result = $conn->query($sql)) {
    // Tính tổng số lượng sản phẩm trong giỏ hàng
    $total_items = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $quantity) {
            $total_items += $quantity;
        }
    }
} else {
    die('Lỗi truy vấn: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Welcome to the Crazy Shop</h1>
        <p>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

        <!-- Navigation Bar -->
        <div class="d-flex justify-content-between mb-4">
            <p>Giỏ hàng: <?php echo $total_items; ?> sản phẩm</p>
            <div>
                <a href="cart.php" class="btn btn-primary">Xem Giỏ hàng</a>
                <a href="history.php" class="btn btn-info">Lịch sử mua hàng</a>
                <a href="user.php" class="btn btn-danger">Thông Tin Cá Nhân</a>
                <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
            </div>
        </div>

        <!-- Search Form -->
        <h2>Tìm kiếm sản phẩm</h2>
        <form method="POST" action="index.php" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search_keyword" placeholder="Tìm kiếm theo tên hoặc mô tả" value="<?php echo htmlspecialchars($search_keyword); ?>">
                <input type="number" class="form-control" name="min_price" placeholder="Giá tối thiểu" value="<?php echo htmlspecialchars($min_price); ?>">
                <input type="number" class="form-control" name="max_price" placeholder="Giá tối đa" value="<?php echo htmlspecialchars($max_price); ?>">
                <button type="submit" class="btn btn-success">Tìm kiếm</button>
            </div>
        </form>

        <h2>Sản phẩm hiện có</h2>
        <div class="row">
            <?php if (isset($result) && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img src="admin/<?php echo htmlspecialchars($row['path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="card-text text-danger"><?php echo number_format($row['price'], 0, ',', '.'); ?> VND</p>
                                <form method="POST" action="add_to_cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-primary">Bỏ vào giỏ hàng</button>
                                </form>
                                <!-- View details button -->
                                <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-info mt-2">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không có sản phẩm nào.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between mt-4">
            <p>Trang hiện tại: <?php echo $page; ?> / <?php echo $total_pages; ?></p>
            <div>
                <?php if ($page > 1): ?>
                    <a href="?page=1" class="btn btn-secondary">&laquo; Đầu</a>
                    <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary">Trước</a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary">Tiếp</a>
                    <a href="?page=<?php echo $total_pages; ?>" class="btn btn-secondary">Cuối &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    
     <p><a href="index.php">Quay lại</a></p>
</body>
</html>

<?php
$conn->close();
?>
