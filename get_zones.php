<?php
// 1. 設定 Header 為 JSON 格式，告訴前端這會回傳 JSON 資料
header('Content-Type: application/json; charset=utf-8');

require 'db_connect.php';

// 2. 接收從前端發來的 concert_id
$concert_id = $_GET['concert_id'] ?? null;

if (!$concert_id) {
    echo json_encode(['status' => 'error', 'message' => '缺少活動編號']);
    exit;
}

try {
    // 3. 查詢資料庫：撈出該活動的所有區域
    // 根據你的資料庫結構，event_id 對應的是 concert_id
    $stmt = $pdo->prepare("SELECT zone_id, zone_name, price, total_seats, available_seats, event_name
                           FROM ticket_zones join events on ticket_zones.event_id = events.event_id
                           WHERE events.event_id = ?");
    $stmt->execute([$concert_id]);
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $first_event_name = !empty($zones) ? $zones[0]['event_name'] : '活動名稱';

    // 4. 回傳結果給前端
    echo json_encode([
        'status' => 'success',
        'event_name' => $first_event_name,
        'data' => $zones
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫讀取失敗：' . $e->getMessage()]);
}
?>