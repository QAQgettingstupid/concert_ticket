<?php
// get_orders.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'db_connect.php'; 

// 2. 檢查登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 3. 撈取訂單主檔 (Query 1)
    $stmt = $pdo->prepare("SELECT order_no, total_amount, payment_status, created_at FROM orders WHERE identity_id = :user_id ORDER BY created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. 跑迴圈去撈每筆訂單的明細 (Query 2)
    foreach ($orders as &$order) {
        // 順便幫前端做好基本加工
        $order['is_unpaid'] = ($order['payment_status'] === '未付款');
        $order['formatted_total'] = number_format($order['total_amount']);

        // 撈明細
        $itemStmt = $pdo->prepare("
                    SELECT 
                        order_items.item_type, 
                        order_items.quantity, 
                        order_items.unit_price, 
                        order_items.attendee_name, 
                        order_items.attendee_identity_no,
                        merchandises.prod_name,
                        ticket_zones.zone_name,
                        event_name
                    FROM order_items
                    LEFT JOIN merchandises ON order_items.merchandise_id = merchandises.merchandise_id
                    LEFT JOIN ticket_zones ON order_items.zone_id = ticket_zones.zone_id
                    LEFT JOIN event_dates ON ticket_zones.date_id = event_dates.date_id
                    LEFT JOIN events ON event_dates.event_id = events.event_id
                    WHERE order_no = :order_no
                ");
        $itemStmt->execute(['order_no' => $order['order_no']]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        // 加工明細資料
        foreach ($items as &$item) {
            $item['is_ticket'] = (strtolower($item['item_type']) === 'ticket');
            $item['formatted_unit_price'] = number_format($item['unit_price']);
            $item['formatted_subtotal'] = number_format($item['unit_price'] * $item['quantity']);
            
            // 簡易遮蔽身分證
            if ($item['is_ticket'] && !empty($item['attendee_identity_no'])) {
                $item['masked_id'] = substr($item['attendee_identity_no'], 0, 3) . '***' . substr($item['attendee_identity_no'], 6);
            } else {
                $item['masked_id'] = '';
            }
        }
        $order['items'] = $items;
    }

    // 5. 撈完直接噴出 JSON，大功告成！
    echo json_encode(['status' => 'success', 'data' => $orders]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'SQL錯誤：' . $e->getMessage()]);
}
?>