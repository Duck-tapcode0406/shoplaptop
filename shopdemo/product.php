<?php
include 'includes/db.php';
include 'includes/header.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM product WHERE id = $id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo '<h2>' . $product['name'] . '</h2>';
        echo '<p>' . $product['description'] . '</p>';
    } else {
        echo '<p>Sản phẩm không tồn tại.</p>';
    }
} else {
    echo '<p>ID sản phẩm không hợp lệ.</p>';
}

include 'includes/footer.php';
?>
