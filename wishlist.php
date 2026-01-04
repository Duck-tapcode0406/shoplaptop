<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if wishlist table exists
$wishlist_table_exists = false;
$check_table = $conn->query("SHOW TABLES LIKE 'wishlist'");
if ($check_table && $check_table->num_rows > 0) {
    $wishlist_table_exists = true;
}

// Fetch wishlist items
$wishlist_items = [];
if ($wishlist_table_exists) {
    $wishlist_query = "
        SELECT w.*, p.name, p.description, pr.price, pr.temporary_price, pr.discount_start, pr.discount_end, img.path
        FROM wishlist w
        JOIN product p ON w.product_id = p.id
        LEFT JOIN price pr ON p.id = pr.product_id
        LEFT JOIN image img ON p.id = img.product_id AND img.sort_order = 1
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ";
    $stmt = $conn->prepare($wishlist_query);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $wishlist_items[] = $row;
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Yêu Thích - DuckShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .wishlist-header {
            margin-bottom: var(--space-3xl);
            padding-bottom: var(--space-lg);
            border-bottom: 2px solid var(--border-color);
        }

        .wishlist-header h1 {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin: 0;
        }

        .wishlist-header i {
            color: #E74C3C;
            font-size: 32px;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--space-xl);
            margin-bottom: var(--space-5xl);
        }

        .wishlist-item {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
            position: relative;
        }

        .wishlist-item:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        .wishlist-item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--radius-md);
            margin-bottom: var(--space-md);
        }

        .wishlist-item-name {
            font-size: var(--fs-h5);
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-sm);
            color: var(--text-primary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wishlist-item-name a {
            color: var(--text-primary);
            text-decoration: none;
        }

        .wishlist-item-name a:hover {
            color: #E74C3C;
        }

        .wishlist-item-price {
            font-size: var(--fs-h4);
            font-weight: var(--fw-bold);
            color: #E74C3C;
            margin-bottom: var(--space-lg);
        }

        .wishlist-item-actions {
            display: flex;
            gap: var(--space-sm);
        }

        .wishlist-item-actions .btn {
            flex: 1;
        }

        .remove-wishlist-btn {
            position: absolute;
            top: var(--space-md);
            right: var(--space-md);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #E74C3C;
            font-size: 18px;
            transition: all var(--transition-fast);
            box-shadow: var(--shadow-sm);
        }

        .remove-wishlist-btn:hover {
            background: #E74C3C;
            color: white;
            transform: scale(1.1);
        }

        .empty-wishlist {
            text-align: center;
            padding: var(--space-5xl);
        }

        .empty-wishlist-icon {
            font-size: 80px;
            color: var(--text-light);
            margin-bottom: var(--space-lg);
        }

        .empty-wishlist h2 {
            margin-bottom: var(--space-md);
        }

        .empty-wishlist p {
            color: var(--text-secondary);
            margin-bottom: var(--space-3xl);
        }

        @media (max-width: 768px) {
            .wishlist-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: var(--space-md);
            }
        }
    </style>
</head>
<body>
    <div class="wishlist-container">
        <div class="wishlist-header">
            <h1>
                <i class="fas fa-heart"></i>
                Danh Sách Yêu Thích
            </h1>
        </div>

        <?php if (!$wishlist_table_exists): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Hệ thống danh sách yêu thích đang được thiết lập. Vui lòng chạy file <code>database_wishlist_addresses.sql</code> để kích hoạt tính năng này.
            </div>
        <?php elseif (count($wishlist_items) > 0): ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist_items as $item): ?>
                    <?php
                    $current_time = date('Y-m-d H:i:s');
                    $on_sale = false;
                    $display_price = $item['price'];
                    if ($item['discount_start'] && $item['discount_end']) {
                        $on_sale = ($current_time >= $item['discount_start'] && $current_time <= $item['discount_end']);
                        $display_price = $on_sale ? $item['temporary_price'] : $item['price'];
                    }
                    ?>
                    <div class="wishlist-item">
                        <button class="remove-wishlist-btn" onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                        <img src="admin/<?php echo htmlspecialchars($item['path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="wishlist-item-image">
                        <h3 class="wishlist-item-name">
                            <a href="product_detail.php?id=<?php echo $item['product_id']; ?>">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                        </h3>
                        <div class="wishlist-item-price">
                            <?php echo number_format($display_price, 0, ',', '.'); ?> ₫
                            <?php if ($on_sale): ?>
                                <span style="font-size: var(--fs-small); color: var(--text-secondary); text-decoration: line-through; margin-left: var(--space-sm);">
                                    <?php echo number_format($item['price'], 0, ',', '.'); ?> ₫
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="wishlist-item-actions">
                            <form method="POST" action="add_to_cart.php" style="flex: 1;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                </button>
                            </form>
                            <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-wishlist">
                <div class="empty-wishlist-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h2>Danh sách yêu thích trống</h2>
                <p>Bạn chưa có sản phẩm nào trong danh sách yêu thích. Hãy thêm sản phẩm yêu thích để xem lại sau!</p>
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function removeFromWishlist(productId) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
                return;
            }

            fetch('remove_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            });
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>





