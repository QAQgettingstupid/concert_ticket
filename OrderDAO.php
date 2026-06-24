<?php
function deleteOrder($pdo, $order_no) {
    $stmtItems = $pdo->prepare("DELETE FROM order_items WHERE order_no = ?");
    $stmtItems->execute([$order_no]);
    
    $stmtOrder = $pdo->prepare("DELETE FROM orders WHERE order_no = ?");
    $stmtOrder->execute([$order_no]);
}

function getOrderByNo($pdo, $order_no, $user_id) {
    $stmt = $pdo->prepare("SELECT order_no FROM orders 
                           WHERE order_no = ? AND identity_id = ?");
    $stmt->execute([$order_no, $user_id]);
    return $stmt->fetch();
}
