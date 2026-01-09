<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';

// Check if user is admin (for view-only mode)
$is_admin_view_mode = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $admin_check = $conn->prepare("SELECT is_admin FROM user WHERE id = ?");
    $admin_check->bind_param('i', $user_id);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    $admin_data = $admin_result ? $admin_result->fetch_assoc() : null;
    $is_admin_view_mode = ($admin_data && $admin_data['is_admin'] == 1);
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$product_id) {
    header('Location: index.php');
    exit();
}

// Fetch product details
$query = "
SELECT 
    p.id, p.name, p.description,
    pr.price, pr.temporary_price, pr.discount_start, pr.discount_end
FROM product p
LEFT JOIN price pr ON p.id = pr.product_id
WHERE p.id = ?
LIMIT 1
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die('Error preparing query: ' . $conn->error);
}

$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$product = $result->fetch_assoc();

// Fetch all images for this product
$images_query = "SELECT id, path, sort_order FROM image WHERE product_id = ? ORDER BY sort_order ASC";
$images_stmt = $conn->prepare($images_query);
$images_stmt->bind_param('i', $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();
$product_images = [];

while ($img = $images_result->fetch_assoc()) {
    $rawPath = trim($img['path']);
    if (!empty($rawPath)) {
        if (strpos($rawPath, 'uploads/') === 0) {
            $imagePath = 'admin/' . $rawPath;
        } else {
            $imagePath = 'admin/uploads/' . $rawPath;
        }
        $product_images[] = [
            'id' => $img['id'],
            'path' => $imagePath,
            'sort_order' => $img['sort_order']
        ];
    }
}

// If no images, use default
if (empty($product_images)) {
    $product_images[] = [
        'id' => 0,
        'path' => 'images/no-image.png',
        'sort_order' => 0
    ];
}

// Check if on sale
$current_time = date('Y-m-d H:i:s');
$on_sale = false;
$display_price = $product['price'];
$original_price = $product['price'];
$discount_percent = 0;

if ($product['discount_start'] && $product['discount_end']) {
    $on_sale = ($current_time >= $product['discount_start'] && $current_time <= $product['discount_end']);
    $display_price = $on_sale ? $product['temporary_price'] : $product['price'];
    $original_price = $product['price'];
    if ($on_sale && $original_price > 0) {
        $discount_percent = round((($original_price - $display_price) / $original_price) * 100);
    }
}

// Check if reviews table exists
$reviews_table_exists = false;
$check_table = $conn->query("SHOW TABLES LIKE 'reviews'");
if ($check_table && $check_table->num_rows > 0) {
    $reviews_table_exists = true;
}

// Fetch reviews (only if table exists)
$reviews = [];
$avg_rating = 0;
$total_reviews = 0;
$user_reviewed = false;

if ($reviews_table_exists) {
    // Fetch reviews
    $reviews_query = "
        SELECT r.*, u.username, u.familyname, u.firstname
        FROM reviews r
        JOIN user u ON r.user_id = u.id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ";
    $reviews_stmt = $conn->prepare($reviews_query);
    if ($reviews_stmt) {
        $reviews_stmt->bind_param('i', $product_id);
        $reviews_stmt->execute();
        $reviews_result = $reviews_stmt->get_result();
        while ($row = $reviews_result->fetch_assoc()) {
            $reviews[] = $row;
        }
    }

    // Calculate average rating
    $avg_rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ?";
    $avg_stmt = $conn->prepare($avg_rating_query);
    if ($avg_stmt) {
        $avg_stmt->bind_param('i', $product_id);
        $avg_stmt->execute();
        $avg_result = $avg_stmt->get_result();
        $rating_stats = $avg_result->fetch_assoc();
        $avg_rating = $rating_stats['avg_rating'] ? round($rating_stats['avg_rating'], 1) : 0;
        $total_reviews = $rating_stats['total_reviews'] ? intval($rating_stats['total_reviews']) : 0;
    }

    // Check if user has already reviewed
    if (isset($_SESSION['user_id'])) {
        $check_reviewed = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
        if ($check_reviewed) {
            $check_reviewed->bind_param('ii', $product_id, $_SESSION['user_id']);
            $check_reviewed->execute();
            $user_reviewed = $check_reviewed->get_result()->num_rows > 0;
        }
    }
}

// Check if product is in wishlist
$in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    // Check if wishlist table exists
    $check_wishlist_table = $conn->query("SHOW TABLES LIKE 'wishlist'");
    if ($check_wishlist_table && $check_wishlist_table->num_rows > 0) {
        $user_id = $_SESSION['user_id'];
        $check_wishlist = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        if ($check_wishlist) {
            $check_wishlist->bind_param('ii', $user_id, $product_id);
            $check_wishlist->execute();
            $wishlist_result = $check_wishlist->get_result();
            $in_wishlist = $wishlist_result->num_rows > 0;
            $check_wishlist->close();
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
    <title><?php echo htmlspecialchars($product['name']); ?> - DNQDH Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .product-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--space-md);
        }

        .breadcrumb-nav {
            display: flex;
            gap: var(--space-md);
            align-items: center;
            margin-bottom: var(--space-2xl);
            font-size: var(--fs-small);
            color: var(--text-secondary);
        }

        .breadcrumb-nav a {
            color: var(--primary);
        }

        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-3xl);
            margin-bottom: var(--space-5xl);
        }

        .product-gallery {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
        }

        .main-image-container {
            position: relative;
            width: 100%;
            height: 500px;
            margin-bottom: var(--space-lg);
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: var(--bg-primary);
            cursor: zoom-in;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .main-image-container:hover .main-image {
            transform: scale(1.05);
        }

        .image-navigation {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            color: var(--text-primary);
            transition: all 0.3s;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .image-navigation:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .image-navigation.prev {
            left: 15px;
        }

        .image-navigation.next {
            right: 15px;
        }

        .image-navigation:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .image-counter {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: var(--fs-small);
            z-index: 10;
        }

        .thumbnail-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            background: var(--bg-primary);
            border-radius: var(--radius-md);
        }

        .thumbnail-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: var(--radius-md);
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
            background: white;
        }

        .thumbnail-item:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
        }

        .thumbnail-item.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(108, 92, 231, 0.3);
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .thumbnail-item:hover .thumbnail-overlay {
            opacity: 1;
        }

        .thumbnail-overlay i {
            color: white;
            font-size: 24px;
        }

        /* Lightbox Modal */
        .lightbox-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .lightbox-modal.active {
            display: flex;
        }

        .lightbox-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-image {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .lightbox-nav.prev {
            left: 20px;
        }

        .lightbox-nav.next {
            right: 20px;
        }

        .lightbox-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: var(--fs-large);
            background: rgba(0, 0, 0, 0.5);
            padding: 10px 20px;
            border-radius: 20px;
        }

        .product-info-section {
            padding: var(--space-lg);
        }

        .product-title {
            font-size: var(--fs-h2);
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-md);
            color: var(--text-primary);
        }

        .product-rating-section {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space-lg);
            border-bottom: 1px solid var(--border-color);
        }

        .stars {
            color: #FFB800;
            font-size: 18px;
        }

        .review-count {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .product-price-section {
            display: flex;
            align-items: center;
            gap: var(--space-lg);
            margin-bottom: var(--space-lg);
            padding: var(--space-lg);
            background-color: var(--bg-primary);
            border-radius: var(--radius-lg);
        }

        .price-original {
            font-size: var(--fs-small);
            color: var(--text-secondary);
            text-decoration: line-through;
        }

        .price-current {
            font-size: var(--fs-h1);
            font-weight: var(--fw-bold);
            color: var(--primary);
        }

        .price-discount {
            background-color: var(--accent);
            color: white;
            padding: var(--space-xs) var(--space-md);
            border-radius: var(--radius-full);
            font-weight: var(--fw-bold);
            font-size: var(--fs-small);
        }

        .stock-status {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-weight: var(--fw-bold);
            margin-bottom: var(--space-lg);
            padding: var(--space-md);
            background-color: #F0FFF4;
            border-radius: var(--radius-md);
            border-left: 4px solid var(--success);
        }

        .description-section {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--space-3xl);
        }

        .tabs {
            display: flex;
            gap: var(--space-md);
            border-bottom: 2px solid var(--border-color);
            margin-bottom: var(--space-lg);
        }

        .tab-btn {
            padding: var(--space-md);
            border: none;
            background: none;
            cursor: pointer;
            font-weight: var(--fw-medium);
            color: var(--text-secondary);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            margin-bottom: -2px;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Reviews Styles */
        .rating-summary {
            background: var(--bg-primary);
            padding: var(--space-3xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-3xl);
        }

        .rating-overview {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: var(--space-3xl);
            align-items: center;
        }

        .rating-score {
            text-align: center;
        }

        .rating-number {
            font-size: 48px;
            font-weight: var(--fw-bold);
            color: #E74C3C;
            display: block;
            margin-bottom: var(--space-sm);
        }

        .rating-stars-display {
            color: #FFB800;
            font-size: 24px;
            margin-bottom: var(--space-sm);
        }

        .rating-count {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .rating-breakdown {
            flex: 1;
        }

        .rating-bar-item {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-sm);
        }

        .rating-label {
            width: 60px;
            font-size: var(--fs-small);
            color: var(--text-secondary);
        }

        .rating-bar {
            flex: 1;
            height: 8px;
            background: var(--border-color);
            border-radius: var(--radius-full);
            overflow: hidden;
        }

        .rating-bar-fill {
            height: 100%;
            background: #FFB800;
            transition: width 0.3s ease;
        }

        .rating-percentage {
            width: 40px;
            text-align: right;
            font-size: var(--fs-small);
            color: var(--text-secondary);
        }

        .review-form-container {
            background: white;
            padding: var(--space-3xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-3xl);
            box-shadow: var(--shadow-sm);
        }

        .review-form-container h4 {
            margin-bottom: var(--space-lg);
            color: var(--text-primary);
        }

        .rating-stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: var(--space-xs);
            margin-bottom: var(--space-lg);
        }

        .rating-stars input[type="radio"] {
            display: none;
        }

        .rating-stars label {
            font-size: 28px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #FFB800;
        }

        .rating-stars input[type="radio"]:checked ~ label {
            color: #FFB800;
        }

        .review-form-container textarea {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-body);
            font-family: inherit;
            resize: vertical;
            transition: border-color var(--transition-fast);
        }

        .review-form-container textarea:focus {
            outline: none;
            border-color: #E74C3C;
        }

        .reviews-list h4 {
            margin-bottom: var(--space-lg);
            color: var(--text-primary);
        }

        .review-item {
            background: white;
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow-xs);
            border: 1px solid var(--border-color);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--space-md);
        }

        .review-user {
            display: flex;
            gap: var(--space-md);
            align-items: flex-start;
        }

        .review-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .review-user-info strong {
            display: block;
            margin-bottom: var(--space-xs);
            color: var(--text-primary);
        }

        .review-rating {
            color: #FFB800;
            font-size: 14px;
        }

        .review-date {
            color: var(--text-secondary);
            font-size: var(--fs-small);
        }

        .review-content {
            margin-bottom: var(--space-md);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .review-actions {
            display: flex;
            gap: var(--space-sm);
            flex-wrap: wrap;
            padding-top: var(--space-md);
            border-top: 1px solid var(--border-color);
            margin-top: var(--space-md);
        }

        .review-action-btn {
            background: none;
            border: 1px solid var(--border-color);
            padding: 8px 14px;
            border-radius: var(--radius-md);
            cursor: pointer;
            color: var(--text-secondary);
            font-size: var(--fs-small);
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .review-action-btn:hover {
            background: var(--bg-primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-xs);
        }

        .review-action-btn i {
            font-size: 14px;
        }

        /* Like Button */
        .review-like-btn,
        .like-btn {
            border-color: #e0e0e0;
        }

        .review-like-btn:hover,
        .like-btn:hover {
            border-color: #00b894;
            color: #00b894;
            background: #e8f8f5;
        }

        .review-like-btn.active,
        .like-btn.active {
            background: linear-gradient(135deg, #00b894, #00cec9);
            border-color: #00b894;
            color: white;
        }

        .review-like-btn.active:hover,
        .like-btn.active:hover {
            background: linear-gradient(135deg, #00a085, #00b894);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
        }

        /* Dislike Button */
        .dislike-btn {
            border-color: #e0e0e0;
        }

        .dislike-btn:hover {
            border-color: #e74c3c;
            color: #e74c3c;
            background: #fdf2f2;
        }

        .dislike-btn.active {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border-color: #e74c3c;
            color: white;
        }

        .dislike-btn.active:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        /* Reply Button */
        .reply-btn {
            border-color: #3498db;
            color: #3498db;
        }

        .reply-btn:hover {
            background: #e3f2fd;
            border-color: #2980b9;
            color: #2980b9;
        }

        /* Count badges */
        .like-count,
        .dislike-count {
            background: rgba(0, 0, 0, 0.05);
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }

        .review-like-btn.active .like-count,
        .like-btn.active .like-count {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .dislike-btn.active .dislike-count {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        /* Reply Form */
        .reply-form-container {
            margin-top: var(--space-lg);
            padding: var(--space-lg);
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reply-form textarea {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--fs-small);
            font-family: var(--font-family);
            resize: vertical;
            min-height: 80px;
            transition: border-color var(--transition-fast);
        }

        .reply-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .reply-form-actions {
            display: flex;
            gap: var(--space-sm);
            margin-top: var(--space-md);
        }

        .reply-form-actions button {
            padding: 10px 18px;
            border-radius: var(--radius-md);
            font-size: var(--fs-small);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cancel-reply-btn {
            background: var(--bg-tertiary);
            color: var(--text-secondary);
        }

        .cancel-reply-btn:hover {
            background: #e0e0e0;
            color: var(--text-primary);
        }

        /* Replies List */
        .replies-list {
            margin-top: var(--space-lg);
            padding-left: var(--space-xl);
            border-left: 3px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }

        .reply-item {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-xs);
        }

        .reply-header {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-sm);
        }

        .reply-author-details {
            flex: 1;
        }

        .reply-author {
            font-weight: 600;
            color: var(--text-primary);
            font-size: var(--fs-small);
            margin-bottom: 2px;
        }

        .reply-date {
            font-size: 11px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .reply-content {
            color: var(--text-primary);
            font-size: var(--fs-small);
            line-height: 1.6;
            margin: 0;
        }

        .delete-reply-btn {
            padding: 4px 8px;
            font-size: 11px;
            background: #fee;
            color: #e74c3c;
            border: 1px solid #fcc;
        }

        .delete-reply-btn:hover {
            background: #fcc;
            border-color: #e74c3c;
        }

        .no-reviews {
            text-align: center;
            padding: var(--space-5xl);
            color: var(--text-secondary);
        }

        .no-reviews i {
            font-size: 48px;
            margin-bottom: var(--space-md);
            color: var(--text-secondary);
        }

        .alert {
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
        }

        .alert-info {
            background: #E3F2FD;
            color: #1976D2;
            border: 1px solid #90CAF9;
        }

        .alert-warning {
            background: #FFF3E0;
            color: #F57C00;
            border: 1px solid #FFB74D;
        }

        .alert-warning a {
            color: #E74C3C;
            font-weight: var(--fw-bold);
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: var(--space-lg);
            }

            .main-image-container {
                height: 350px;
            }

            .image-navigation {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .image-navigation.prev {
                left: 10px;
            }

            .image-navigation.next {
                right: 10px;
            }

            .thumbnail-gallery {
                grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
                gap: 8px;
                max-height: 300px;
            }

            .lightbox-nav {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }

            .lightbox-nav.prev {
                left: 10px;
            }

            .lightbox-nav.next {
                right: 10px;
            }

            .main-image {
                height: 300px;
            }

            .rating-overview {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .review-header {
                flex-direction: column;
                gap: var(--space-sm);
            }

            .review-actions {
                flex-wrap: wrap;
                gap: var(--space-xs);
            }

            .review-action-btn {
                padding: 6px 12px;
                font-size: 12px;
            }

            .reply-form-container {
                padding: var(--space-md);
            }

            .replies-list {
                padding-left: var(--space-md);
            }
        }
    </style>
</head>
<body>
    <div class="product-detail-container">
        <!-- Back Button -->
        <button onclick="window.history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </button>
        
        <!-- Breadcrumb -->
        <div class="breadcrumb-nav">
            <a href="index.php"><i class="fas fa-home"></i> Trang Chủ</a>
            <i class="fas fa-chevron-right"></i>
            <a href="product.php">Sản Phẩm</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <!-- Product Details Grid -->
        <div class="product-grid">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <!-- Main Image Container -->
                <div class="main-image-container" id="mainImageContainer">
                    <img src="<?php echo htmlspecialchars($product_images[0]['path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="main-image" 
                         id="mainImage"
                         onerror="this.onerror=null; this.src='images/no-image.png';">
                    
                    <!-- Navigation Buttons -->
                    <?php if (count($product_images) > 1): ?>
                    <button class="image-navigation prev" id="prevImage" onclick="changeImage(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="image-navigation next" id="nextImage" onclick="changeImage(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="image-counter" id="imageCounter">
                        1 / <?php echo count($product_images); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Thumbnail Gallery -->
                <?php if (count($product_images) > 1): ?>
                <div class="thumbnail-gallery" id="thumbnailGallery">
                    <?php foreach ($product_images as $index => $img): ?>
                    <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                         data-index="<?php echo $index; ?>"
                         onclick="selectImage(<?php echo $index; ?>)">
                        <img src="<?php echo htmlspecialchars($img['path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.onerror=null; this.src='images/no-image.png';">
                        <div class="thumbnail-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Lightbox Modal -->
            <div class="lightbox-modal" id="lightboxModal">
                <button class="lightbox-close" onclick="closeLightbox()">
                    <i class="fas fa-times"></i>
                </button>
                <?php if (count($product_images) > 1): ?>
                <button class="lightbox-nav prev" onclick="changeLightboxImage(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="lightbox-nav next" onclick="changeLightboxImage(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="lightbox-counter" id="lightboxCounter">
                    1 / <?php echo count($product_images); ?>
                </div>
                <?php endif; ?>
                <div class="lightbox-content">
                    <img src="" alt="<?php echo htmlspecialchars($product['name']); ?>" class="lightbox-image" id="lightboxImage">
                </div>
            </div>

            <!-- Product Information -->
            <div class="product-info-section">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                <!-- Rating -->
                <div class="product-rating-section">
                    <div class="stars">
                        <?php
                        $full_stars = floor($avg_rating);
                        $has_half = ($avg_rating - $full_stars) >= 0.5;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $full_stars) {
                                echo '<i class="fas fa-star"></i>';
                            } elseif ($i == $full_stars + 1 && $has_half) {
                                echo '<i class="fas fa-star-half-alt"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="review-count"><?php echo $avg_rating; ?> (<?php echo $total_reviews; ?> đánh giá)</span>
                </div>

                <!-- Price Section -->
                <div class="product-price-section">
                    <?php if ($on_sale): ?>
                        <span class="price-original"><?php echo number_format($original_price, 0, ',', '.'); ?> ₫</span>
                    <?php endif; ?>
                    <span class="price-current"><?php echo number_format($display_price, 0, ',', '.'); ?> ₫</span>
                    <?php if ($on_sale): ?>
                        <span class="price-discount">-<?php echo $discount_percent; ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Stock Status -->
                <div class="stock-status">
                    <i class="fas fa-check-circle"></i>
                    <span>Còn hàng - Giao hàng nhanh 24-48 giờ</span>
                </div>

                <!-- Add to Cart Form (Hidden for admin view mode) -->
                <?php if (!$is_admin_view_mode): ?>
                <form method="POST" action="add_to_cart.php" style="margin-bottom: var(--space-lg);">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div style="margin-bottom: var(--space-lg);">
                        <label style="display: block; margin-bottom: var(--space-sm); font-weight: var(--fw-bold);">
                            Số lượng:
                        </label>
                        <div style="display: flex; align-items: center; gap: var(--space-md);">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="decreaseQty()">−</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" style="width: 60px; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-md); text-align: center;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="increaseQty()">+</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: var(--space-md);">
                        <i class="fas fa-shopping-cart"></i> Thêm vào Giỏ Hàng
                    </button>
                </form>
                <?php else: ?>
                <div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; margin-bottom: var(--space-lg); text-align: center;">
                    <i class="fas fa-info-circle" style="color: #856404; font-size: 24px; margin-bottom: 10px;"></i>
                    <p style="color: #856404; margin: 0; font-weight: 500;">Chế độ xem Admin - Không thể mua hàng</p>
                    <a href="admin/index.php" class="btn btn-secondary" style="margin-top: 15px;">
                        <i class="fas fa-tachometer-alt"></i> Quay về trang Admin
                    </a>
                </div>
                <?php endif; ?>

                <button class="btn btn-secondary btn-lg" id="wishlist-btn" onclick="toggleWishlist(<?php echo $product_id; ?>)" style="width: 100%; margin-bottom: var(--space-lg);">
                    <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i> 
                    <span id="wishlist-text"><?php echo $in_wishlist ? 'Đã thêm vào' : 'Thêm vào'; ?> Danh Sách Yêu Thích</span>
                </button>

                <!-- Trust Badges -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md); padding-top: var(--space-lg); border-top: 1px solid var(--border-color);">
                    <div style="text-align: center;">
                        <i class="fas fa-shield-alt" style="font-size: 24px; color: var(--success); margin-bottom: var(--space-sm); display: block;"></i>
                        <strong>Bảo vệ Người Mua</strong>
                        <p style="font-size: var(--fs-small); color: var(--text-secondary);">100% bảo vệ quyền lợi</p>
                    </div>
                    <div style="text-align: center;">
                        <i class="fas fa-undo" style="font-size: 24px; color: var(--secondary); margin-bottom: var(--space-sm); display: block;"></i>
                        <strong>Hoàn Trả Dễ Dàng</strong>
                        <p style="font-size: var(--fs-small); color: var(--text-secondary);">30 ngày hoàn trả miễn phí</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Description & Specs -->
        <div class="description-section">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab(event, 'description')">
                    <i class="fas fa-align-left"></i> Mô Tả
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'specs')">
                    <i class="fas fa-list"></i> Thông Số Kỹ Thuật
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'reviews')">
                    <i class="fas fa-star"></i> Đánh Giá (<?php echo $total_reviews; ?>)
                </button>
            </div>

            <!-- Description Tab -->
            <div id="description" class="tab-content active">
                <h3>Mô Tả Sản Phẩm</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả cho sản phẩm này.')); ?></p>
            </div>

            <!-- Specs Tab -->
            <div id="specs" class="tab-content">
                <h3>Thông Số Kỹ Thuật</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: var(--space-md); font-weight: var(--fw-bold); width: 30%;">Mã Sản Phẩm:</td>
                        <td style="padding: var(--space-md);"><?php echo $product['id']; ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: var(--space-md); font-weight: var(--fw-bold);">Danh Mục:</td>
                        <td style="padding: var(--space-md);">Sản Phẩm Chính</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: var(--space-md); font-weight: var(--fw-bold);">Thương Hiệu:</td>
                        <td style="padding: var(--space-md);">DNQDH Shop</td>
                    </tr>
                </table>
            </div>

            <!-- Reviews Tab -->
            <div id="reviews" class="tab-content">
                <?php if ($reviews_table_exists): ?>
                <!-- Rating Summary -->
                <div class="rating-summary">
                    <div class="rating-overview">
                        <div class="rating-score">
                            <span class="rating-number"><?php echo $avg_rating; ?></span>
                            <div class="rating-stars-display">
                                <?php
                                $full_stars = floor($avg_rating);
                                $has_half = ($avg_rating - $full_stars) >= 0.5;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $full_stars) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($i == $full_stars + 1 && $has_half) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <p class="rating-count"><?php echo $total_reviews; ?> đánh giá</p>
                        </div>
                        <div class="rating-breakdown">
                            <?php
                            for ($i = 5; $i >= 1; $i--) {
                                $count_query = "SELECT COUNT(*) as count FROM reviews WHERE product_id = ? AND rating = ?";
                                $count_stmt = $conn->prepare($count_query);
                                if ($count_stmt) {
                                    $count_stmt->bind_param('ii', $product_id, $i);
                                    $count_stmt->execute();
                                    $count_result = $count_stmt->get_result();
                                    $count = $count_result->fetch_assoc()['count'];
                                    $percentage = $total_reviews > 0 ? round(($count / $total_reviews) * 100) : 0;
                                } else {
                                    $count = 0;
                                    $percentage = 0;
                                }
                                ?>
                                <div class="rating-bar-item">
                                    <span class="rating-label"><?php echo $i; ?> <i class="fas fa-star"></i></span>
                                    <div class="rating-bar">
                                        <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="rating-percentage"><?php echo $count; ?></span>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Hệ thống đánh giá đang được thiết lập.
                </div>
                <?php endif; ?>

                <!-- Review Form -->
                <?php if ($reviews_table_exists): ?>
                <?php if (isset($_SESSION['user_id']) && !$user_reviewed): ?>
                <div class="review-form-container">
                    <h4><i class="fas fa-star"></i> Viết đánh giá của bạn</h4>
                    <form id="reviewForm" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="form-group">
                            <label>Xếp hạng của bạn: <span id="rating-text" style="color: #f0c14b; font-weight: normal;">Tuyệt vời</span></label>
                            <div class="rating-stars">
                                <input type="radio" id="star5" name="rating" value="5" checked required/>
                                <label for="star5" title="5 sao - Tuyệt vời"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4"/>
                                <label for="star4" title="4 sao - Rất tốt"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3"/>
                                <label for="star3" title="3 sao - Tốt"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2"/>
                                <label for="star2" title="2 sao - Tạm được"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1"/>
                                <label for="star1" title="1 sao - Không hài lòng"><i class="fas fa-star"></i></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reviewContent">Nội dung đánh giá:</label>
                            <textarea id="reviewContent" name="content" rows="5" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này..." required minlength="10"></textarea>
                            <small style="color: var(--text-secondary);">Tối thiểu 10 ký tự</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Gửi đánh giá
                        </button>
                    </form>
                </div>
                <?php elseif (isset($_SESSION['user_id']) && $user_reviewed): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Bạn đã đánh giá sản phẩm này rồi.
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Vui lòng <a href="login.php">đăng nhập</a> để viết đánh giá.
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Reviews List -->
                <?php if ($reviews_table_exists): ?>
                <div class="reviews-list">
                    <h4>Đánh giá từ khách hàng (<?php echo $total_reviews; ?>)</h4>
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-user">
                                        <div class="review-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="review-user-info">
                                            <strong><?php echo htmlspecialchars(($review['familyname'] ?? '') . ' ' . ($review['firstname'] ?? $review['username'])); ?></strong>
                                            <div class="review-rating">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $review['rating']) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="review-date">
                                        <?php
                                        $date = new DateTime($review['created_at']);
                                        $now = new DateTime();
                                        $diff = $now->diff($date);
                                        
                                        if ($diff->days == 0) {
                                            if ($diff->h == 0) {
                                                echo $diff->i . ' phút trước';
                                            } else {
                                                echo $diff->h . ' giờ trước';
                                            }
                                        } elseif ($diff->days < 7) {
                                            echo $diff->days . ' ngày trước';
                                        } elseif ($diff->days < 30) {
                                            echo floor($diff->days / 7) . ' tuần trước';
                                        } elseif ($diff->days < 365) {
                                            echo floor($diff->days / 30) . ' tháng trước';
                                        } else {
                                            echo $date->format('d/m/Y');
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p><?php echo nl2br(htmlspecialchars($review['content'])); ?></p>
                                </div>
                                <div class="review-actions">
                                    <button class="review-action-btn like-btn" data-review-id="<?php echo $review['id']; ?>" title="Hữu ích">
                                        <i class="far fa-thumbs-up"></i>
                                        <span class="like-count">0</span>
                                    </button>
                                    <button class="review-action-btn dislike-btn" data-review-id="<?php echo $review['id']; ?>" title="Không hữu ích">
                                        <i class="far fa-thumbs-down"></i>
                                        <span class="dislike-count">0</span>
                                    </button>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="review-action-btn reply-btn" data-review-id="<?php echo $review['id']; ?>" title="Trả lời">
                                        <i class="far fa-comment"></i>
                                        Trả lời
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Reply Form (Hidden by default) -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="reply-form-container" id="reply-form-<?php echo $review['id']; ?>" style="display: none;">
                                    <form class="reply-form" data-review-id="<?php echo $review['id']; ?>">
                                        <textarea class="reply-content" rows="3" placeholder="Viết câu trả lời của bạn..." required minlength="5"></textarea>
                                        <div class="reply-form-actions">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane"></i> Gửi trả lời
                                            </button>
                                            <button type="button" class="cancel-reply-btn">
                                                Hủy
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Replies List -->
                                <div class="replies-list" id="replies-<?php echo $review['id']; ?>" data-review-id="<?php echo $review['id']; ?>">
                                    <!-- Replies will be loaded here dynamically -->
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-reviews">
                            <i class="fas fa-comment-slash"></i>
                            <p>Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function increaseQty() {
            const qtyInput = document.getElementById('quantity');
            qtyInput.value = parseInt(qtyInput.value) + 1;
        }

        function decreaseQty() {
            const qtyInput = document.getElementById('quantity');
            if (parseInt(qtyInput.value) > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
            }
        }

        function switchTab(event, tabName) {
            event.preventDefault();
            
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.target.closest('.tab-btn').classList.add('active');
        }

        // Toggle Wishlist function
        function toggleWishlist(productId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào danh sách yêu thích.');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            const btn = document.getElementById('wishlist-btn');
            const icon = btn.querySelector('i');
            const text = document.getElementById('wishlist-text');
            const isInWishlist = icon.classList.contains('fas');

            const url = isInWishlist ? 'remove_wishlist.php' : 'add_wishlist.php';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                    if (icon.classList.contains('fas')) {
                        text.textContent = 'Đã thêm vào Danh Sách Yêu Thích';
                    } else {
                        text.textContent = 'Thêm vào Danh Sách Yêu Thích';
                    }
                    if (typeof showSuccess === 'function') {
                        showSuccess('Thành công', data.message);
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thực hiện thao tác');
            });
        }

        // Rating stars interaction
        const ratingInputs = document.querySelectorAll('input[name="rating"]');
        const ratingText = document.getElementById('rating-text');
        const ratingTexts = {
            5: 'Tuyệt vời',
            4: 'Rất tốt',
            3: 'Tốt',
            2: 'Tạm được',
            1: 'Không hài lòng'
        };

        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (ratingText) {
                    ratingText.textContent = ratingTexts[this.value];
                }
            });
        });

        // Review form submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const content = document.getElementById('reviewContent').value.trim();
                if (content.length < 10) {
                    alert('Vui lòng nhập ít nhất 10 ký tự cho nội dung đánh giá.');
                    return false;
                }

                const formData = new FormData(this);
                
                fetch('add_review.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi gửi đánh giá.');
                });
            });
        }

        // Like/Dislike functionality
        document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.dataset.reviewId;
                const action = this.classList.contains('like-btn') ? 'like' : 'dislike';
                const isActive = this.classList.contains('active');
                
                // Toggle active state
                if (isActive) {
                    this.classList.remove('active');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-solid');
                        icon.classList.add('fa-regular');
                    }
                } else {
                    this.classList.add('active');
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-regular');
                        icon.classList.add('fa-solid');
                    }
                    
                    // Remove active from opposite button
                    const oppositeBtn = this.parentElement.querySelector(action === 'like' ? '.dislike-btn' : '.like-btn');
                    if (oppositeBtn && oppositeBtn.classList.contains('active')) {
                        oppositeBtn.classList.remove('active');
                        const oppositeIcon = oppositeBtn.querySelector('i');
                        if (oppositeIcon) {
                            oppositeIcon.classList.remove('fa-solid');
                            oppositeIcon.classList.add('fa-regular');
                        }
                    }
                }
                
                // Update count (visual feedback - bạn có thể tích hợp API thật sau)
                const countSpan = this.querySelector('.like-count, .dislike-count');
                if (countSpan) {
                    let count = parseInt(countSpan.textContent) || 0;
                    count = isActive ? Math.max(0, count - 1) : count + 1;
                    countSpan.textContent = count;
                }
                
                // TODO: Gửi AJAX request đến API để lưu like/dislike
                // fetch('api/review_like.php', {
                //     method: 'POST',
                //     body: JSON.stringify({ review_id: reviewId, action: action, is_active: !isActive })
                // });
            });
        });

        // Reply button functionality
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const reviewId = this.dataset.reviewId;
                const replyForm = document.getElementById('reply-form-' + reviewId);
                
                if (replyForm) {
                    const isVisible = replyForm.style.display !== 'none';
                    replyForm.style.display = isVisible ? 'none' : 'block';
                    
                    if (!isVisible) {
                        replyForm.querySelector('textarea').focus();
                    }
                }
            });
        });

        // Cancel reply button
        document.querySelectorAll('.cancel-reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const formContainer = this.closest('.reply-form-container');
                if (formContainer) {
                    formContainer.style.display = 'none';
                    formContainer.querySelector('textarea').value = '';
                }
            });
        });

        // Reply form submission
        document.querySelectorAll('.reply-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const reviewId = this.dataset.reviewId;
                const content = this.querySelector('.reply-content').value.trim();
                
                if (content.length < 5) {
                    alert('Nội dung trả lời phải có ít nhất 5 ký tự.');
                    return;
                }
                
                // TODO: Gửi AJAX request đến API để lưu reply
                // fetch('api/add_reply.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         review_id: reviewId,
                //         content: content
                //     })
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         // Add reply to replies list
                //         addReplyToDOM(reviewId, data.reply);
                //         form.querySelector('.reply-content').value = '';
                //         form.closest('.reply-form-container').style.display = 'none';
                //     } else {
                //         alert(data.message);
                //     }
                // });
                
                // Temporary: Show success message
                alert('Tính năng trả lời đang được phát triển. Reply sẽ được lưu vào database sau khi tích hợp API.');
            });
        });

        // Image Gallery Functionality
        const productImages = <?php echo json_encode(array_column($product_images, 'path')); ?>;
        let currentImageIndex = 0;

        function changeImage(direction) {
            if (productImages.length <= 1) return;
            
            currentImageIndex += direction;
            
            if (currentImageIndex < 0) {
                currentImageIndex = productImages.length - 1;
            } else if (currentImageIndex >= productImages.length) {
                currentImageIndex = 0;
            }
            
            updateMainImage();
            updateThumbnails();
            updateNavigationButtons();
        }

        function selectImage(index) {
            if (index < 0 || index >= productImages.length) return;
            currentImageIndex = index;
            updateMainImage();
            updateThumbnails();
            updateNavigationButtons();
        }

        function updateMainImage() {
            const mainImage = document.getElementById('mainImage');
            if (mainImage && productImages[currentImageIndex]) {
                mainImage.src = productImages[currentImageIndex];
                mainImage.onerror = function() {
                    this.src = 'images/no-image.png';
                };
            }
            
            // Update counter
            const counter = document.getElementById('imageCounter');
            if (counter) {
                counter.textContent = `${currentImageIndex + 1} / ${productImages.length}`;
            }
        }

        function updateThumbnails() {
            document.querySelectorAll('.thumbnail-item').forEach((item, index) => {
                if (index === currentImageIndex) {
                    item.classList.add('active');
                    // Scroll thumbnail into view
                    item.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevImage');
            const nextBtn = document.getElementById('nextImage');
            
            if (productImages.length <= 1) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
            } else {
                if (prevBtn) prevBtn.style.display = 'flex';
                if (nextBtn) nextBtn.style.display = 'flex';
            }
        }

        // Lightbox functionality
        function openLightbox(index) {
            if (index !== undefined) {
                currentImageIndex = index;
            }
            
            const lightbox = document.getElementById('lightboxModal');
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxCounter = document.getElementById('lightboxCounter');
            
            if (lightbox && lightboxImage) {
                lightboxImage.src = productImages[currentImageIndex];
                lightboxImage.onerror = function() {
                    this.src = 'images/no-image.png';
                };
                
                if (lightboxCounter) {
                    lightboxCounter.textContent = `${currentImageIndex + 1} / ${productImages.length}`;
                }
                
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                updateLightboxNavigation();
            }
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightboxModal');
            if (lightbox) {
                lightbox.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function changeLightboxImage(direction) {
            changeImage(direction);
            
            const lightboxImage = document.getElementById('lightboxImage');
            const lightboxCounter = document.getElementById('lightboxCounter');
            
            if (lightboxImage) {
                lightboxImage.src = productImages[currentImageIndex];
                lightboxImage.onerror = function() {
                    this.src = 'images/no-image.png';
                };
            }
            
            if (lightboxCounter) {
                lightboxCounter.textContent = `${currentImageIndex + 1} / ${productImages.length}`;
            }
            
            updateLightboxNavigation();
        }

        function updateLightboxNavigation() {
            // Navigation buttons are always visible in lightbox for multiple images
        }

        // Click main image to open lightbox
        document.addEventListener('DOMContentLoaded', function() {
            const mainImageContainer = document.getElementById('mainImageContainer');
            if (mainImageContainer) {
                mainImageContainer.addEventListener('click', function() {
                    if (productImages.length > 0) {
                        openLightbox();
                    }
                });
            }

            // Click thumbnail to open lightbox
            document.querySelectorAll('.thumbnail-item').forEach((item, index) => {
                item.addEventListener('dblclick', function() {
                    openLightbox(index);
                });
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                const lightbox = document.getElementById('lightboxModal');
                if (!lightbox || !lightbox.classList.contains('active')) return;

                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowLeft') {
                    changeLightboxImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeLightboxImage(1);
                }
            });

            // Close lightbox when clicking outside image
            const lightboxModal = document.getElementById('lightboxModal');
            if (lightboxModal) {
                lightboxModal.addEventListener('click', function(e) {
                    if (e.target === lightboxModal || e.target.classList.contains('lightbox-content')) {
                        closeLightbox();
                    }
                });
            }

            // Initialize navigation buttons
            updateNavigationButtons();
        });
    </script>
</body>
</html>
