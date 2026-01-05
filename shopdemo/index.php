<?php
session_start();

// Lưu thời gian hiện tại vào session (nếu chưa có)
if (!isset($_SESSION['current_time'])) {
    $_SESSION['current_time'] = date('Y-m-d H:i:s');  // Lưu thời gian hiện tại
}

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

// Lấy giá trị tìm kiếm từ form
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

// Tạo câu lệnh SQL đếm sản phẩm theo tên sản phẩm, mô tả và giá
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
        (p.name LIKE ? OR p.description LIKE ?)";
        
// Thêm điều kiện cho min_price và max_price nếu tìm kiếm theo giá
if ($min_price !== null && $max_price !== null) {
    $count_sql .= " AND pr.price BETWEEN ? AND ?";
} elseif ($min_price !== null) {
    $count_sql .= " AND pr.price >= ?";
} elseif ($max_price !== null) {
    $count_sql .= " AND pr.price <= ?";
}

$stmt = $conn->prepare($count_sql);

// Liên kết các tham số tìm kiếm theo tên và mô tả
$search_param = "%" . $search . "%";  // Thêm dấu % để tìm kiếm phần tên hoặc mô tả sản phẩm chứa từ khóa

// Liên kết các tham số tìm kiếm theo giá
if ($min_price !== null && $max_price !== null) {
    $stmt->bind_param("ssdd", $search_param, $search_param, $min_price, $max_price);
} elseif ($min_price !== null) {
    $stmt->bind_param("ssd", $search_param, $search_param, $min_price);
} elseif ($max_price !== null) {
    $stmt->bind_param("ssd", $search_param, $search_param, $max_price);
} else {
    $stmt->bind_param("ss", $search_param, $search_param);  // Trường hợp không có min_price và max_price
}
$stmt->execute();
$count_result = $stmt->get_result();
$total_products = $count_result->fetch_assoc()['total_products'];

// Xác định số sản phẩm mỗi trang và tổng số trang
$products_per_page = 8;
$total_pages = ceil($total_products / $products_per_page);

// Xác định trang hiện tại (mặc định là trang 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page > 0) ? $page : 1;  // Đảm bảo trang không âm

// Tính chỉ mục bắt đầu cho truy vấn SQL
$offset = ($page - 1) * $products_per_page;

// Truy vấn danh sách sản phẩm theo tên, mô tả và giá
$sql = "
    SELECT 
        p.id,
        p.name,
        p.description,
        pr.price,
        pr.temporary_price,
        pr.discount_start,
        pr.discount_end,
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
        (p.name LIKE ? OR p.description LIKE ?)";
        
// Thêm điều kiện cho min_price và max_price
if ($min_price !== null && $max_price !== null) {
    $sql .= " AND pr.price BETWEEN ? AND ?";
} elseif ($min_price !== null) {
    $sql .= " AND pr.price >= ?";
} elseif ($max_price !== null) {
    $sql .= " AND pr.price <= ?";
}

$sql .= " LIMIT ?, ?";  // Phân trang

$stmt = $conn->prepare($sql);
if ($min_price !== null && $max_price !== null) {
    $stmt->bind_param("ssddii", $search_param, $search_param, $min_price, $max_price, $offset, $products_per_page);
} elseif ($min_price !== null) {
    $stmt->bind_param("ssdi", $search_param, $search_param, $min_price, $offset, $products_per_page);
} elseif ($max_price !== null) {
    $stmt->bind_param("ssdi", $search_param, $search_param, $max_price, $offset, $products_per_page);
} else {
    $stmt->bind_param("ssii", $search_param, $search_param, $offset, $products_per_page);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include('includes/header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VDQ Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Thêm màu nền cho các phần tử */
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .sale-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #FF5733;
            color: white;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn-primary, .btn-success {
            border-radius: 25px;
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        /* Tinh chỉnh màu sắc và bóng của các nút tìm kiếm */
        .search-btn {
            background-color: #28a745;
            color: white;
            border-radius: 25px;
            transition: background-color 0.3s ease;
        }
        .search-btn:hover {
            background-color: #218838;
        }
        .price-tag {
            font-weight: bold;
            font-size: 1.25rem;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center text-primary">Welcome to VDQ Shop</h1>
        <p class="text-center">Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

        <!-- Tìm kiếm theo tên và mô tả -->
        <form class="mb-4" method="GET" action="">
            <div class="d-flex justify-content-center gap-3">
                <input type="text" name="search" class="form-control w-50" placeholder="Tìm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn search-btn">Tìm kiếm</button>
            </div>
        </form>

        <!-- Tìm kiếm theo giá -->
        <form class="mb-4" method="GET" action="">
            <div class="d-flex justify-content-center gap-3">
                <input type="number" name="min_price" class="form-control w-25" placeholder="Giá tối thiểu" value="<?php echo htmlspecialchars($min_price); ?>" min="0">
                <input type="number" name="max_price" class="form-control w-25" placeholder="Giá tối đa" value="<?php echo htmlspecialchars($max_price); ?>" min="0">
                <button type="submit" class="btn search-btn">Tìm theo giá</button>
            </div>
        </form>

        <h2 class="text-center mb-4">Sản phẩm hiện có</h2>
        <div class="row">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Kiểm tra xem sản phẩm có trong khoảng thời gian khuyến mãi không
                    $current_time = $_SESSION['current_time'];
                    $discount_start = $row['discount_start'];
                    $discount_end = $row['discount_end'];

                    if ($current_time >= $discount_start && $current_time <= $discount_end) {
                        $price = $row['temporary_price'];  // Nếu trong khuyến mãi, lấy giá tạm thời
                        $is_on_sale = true;  // Đánh dấu là sản phẩm đang giảm giá
                    } else {
                        $price = $row['price'];  // Nếu ngoài khuyến mãi, lấy giá gốc
                        $is_on_sale = false;  // Không giảm giá
                    }
                    ?>
                    <div class="col-md-3 mb-4">
                        <div class="card position-relative shadow-lg">
                            <?php if ($is_on_sale): ?>
                                <div class="sale-badge">Sale</div>
                            <?php endif; ?>
                            <img src="admin/<?php echo htmlspecialchars($row['path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="price-tag"><?php echo number_format($price, 0, ',', '.'); ?> VND</p>
                                <form method="POST" action="add_to_cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-primary w-100">Bỏ vào giỏ hàng</button>
                                </form>
                                <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-info mt-2 w-100">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">Không có sản phẩm nào.</p>
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
</body>
</html>

<?php $conn->close(); ?>
