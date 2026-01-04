<?php
$product_id = $_GET['product_id'];

$conn = new mysqli("localhost", "root", "", "shop");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT price FROM price WHERE product_id = $product_id LIMIT 1");
$row = $result->fetch_assoc();

echo json_encode(['price' => $row['price']]);

$conn->close();
?>
