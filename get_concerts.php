<?php
// 1. 宣告這是一支 JSON 格式的 API
header('Content-Type: application/json; charset=utf-8');

// 資料庫連線設定（沿用你目前的 company 設定）
$host = '127.0.0.1';
$db   = 'concert_ticket';
$user = 'root';
$pass = 'root'; // 依你實際的密碼填寫（剛剛連線成功是空字串，如果有改就填 'root'）
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 2. 建立連線
    $pdo = new PDO($dsn, $user, $pass, $options);
    
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