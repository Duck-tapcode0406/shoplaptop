<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if (!$category_id) {
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit();
}

// Get attributes for this category (remove duplicates by ID)
$attributes = $conn->query("
    SELECT DISTINCT ca.id, ca.category_id, ca.attribute_name, ca.attribute_type, 
           ca.attribute_options, ca.is_required, ca.sort_order
    FROM category_attributes ca
    WHERE ca.category_id = $category_id 
    ORDER BY ca.sort_order, ca.attribute_name
");

$result = [];
$seen_ids = [];
if ($attributes) {
    while ($row = $attributes->fetch_assoc()) {
        // Double check to avoid duplicates
        if (!in_array($row['id'], $seen_ids)) {
            $seen_ids[] = $row['id'];
            $result[] = $row;
        }
    }
}

echo json_encode([
    'success' => true,
    'attributes' => $result
]);

