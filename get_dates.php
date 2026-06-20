<?php
// 設定回傳格式為 JSON
header('Content-Type: application/json; charset=utf-8');

require 'db_connect.php';

// 接收前端傳來的 concert_id
$concert_id = $_GET['concert_id'] ?? null;

if (!$concert_id) {
    echo json_encode(['status' => 'error', 'message' => '缺少活動編號']);
    exit;
}

try {
    // 假設你的資料表中日期欄位名稱為 concert_date
    $stmt = $pdo->prepare("SELECT DISTINCT event_date 
                           FROM event_dates 
                           WHERE event_id = ? 
                           ORDER BY event_date ASC");
    
    $stmt->execute([$concert_id]);
    
    // 將結果轉為一維陣列 (只取出日期字串)
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'status' => 'success',
        'dates' => $dates
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '無法讀取日期：' . $e->getMessage()]);
}
?>