<?php
// 1. 宣告這是一支 JSON 格式的 API
header('Content-Type: application/json; charset=utf-8');

// 直接引入連線檔案，自動取得 $pdo 變數
require_once 'db_connect.php';

try {
    $sql = "SELECT 
            Events.event_id AS concert_id,
            Events.event_name AS title,
            Events.venue_name AS location,
            GROUP_CONCAT(Event_Dates.event_date ORDER BY Event_Dates.event_date ASC SEPARATOR ', ') AS dates,
            Events.ticket_release_time AS time,
            Events.event_status AS status
        FROM Events JOIN Event_Dates ON Events.event_id = Event_Dates.event_id
        GROUP BY Events.event_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $concerts = $stmt->fetchAll();
    
    // 4. 打包成標準外送盒吐給前端 index.html
    echo json_encode([
        'status' => 'success',
        'message' => '成功從 company 資料庫調出活動清單！',
        'data' => $concerts
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\PDOException $e) {
    // 如果連線或撈取失敗，吐回錯誤訊息
    echo json_encode([
        'status' => 'error',
        'message' => '資料庫連線或撈取失敗：' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}