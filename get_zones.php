<?php
// 1. 設定 Header 為 JSON 格式，告訴前端這會回傳 JSON 資料
header('Content-Type: application/json; charset=utf-8');

require 'db_connect.php';

// 2. 接收從前端發來的 date_id
$date_id = $_GET['date_id'] ?? null;

if (!$date_id) {
    echo json_encode(['status' => 'error', 'message' => '缺少日期編號']);
    exit;
}

try {
    // 3. 查詢資料庫：撈出該日期的所有區域
    $stmt = $pdo->prepare("SELECT zone_id, zone_name, price, total_seats, available_seats
                           FROM ticket_zones
                           WHERE date_id = ?");
    $stmt->execute([$date_id]);
    $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. 回傳結果給前端
    echo json_encode([
        'status' => 'success',
        'data' => $zones
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫讀取失敗：' . $e->getMessage()]);
}
?>