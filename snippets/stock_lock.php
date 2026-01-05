<?php
/**
 * reserve_stock($conn, $product_id, $color_id, $quantity)
 * Trả về true nếu reserve thành công, false nếu không đủ stock
 */
function reserve_stock($conn, $product_id, $color_id, $quantity) {
    // Bắt transaction trước khi gọi hàm này ngoài
    $sel = $conn->prepare('SELECT quantity FROM colors_configuration WHERE product_id = ? AND id = ? FOR UPDATE');
    $sel->bind_param('ii', $product_id, $color_id);
    if (!$sel->execute()) return false;
    $r = $sel->get_result()->fetch_assoc();
    if (!$r || intval($r['quantity']) < $quantity) {
        return false;
    }
    $upd = $conn->prepare('UPDATE colors_configuration SET quantity = quantity - ? WHERE product_id = ? AND id = ?');
    $upd->bind_param('iii', $quantity, $product_id, $color_id);
    if (!$upd->execute()) return false;
    return true;
}
?>

