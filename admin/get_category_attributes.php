<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if (!$category_id) {
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit();
}

// Get attributes for this category
$attributes = $conn->query("
    SELECT * FROM category_attributes 
    WHERE category_id = $category_id 
    ORDER BY sort_order, attribute_name
");

$result = [];
while ($row = $attributes->fetch_assoc()) {
    $result[] = $row;
}

echo json_encode([
    'success' => true,
    'attributes' => $result
]);

