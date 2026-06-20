<?php
$host = '127.0.0.1';
$db   = 'concert_ticket';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 建立連線物件 $pdo
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // 如果是 API 用途，建議吐 JSON 格式錯誤訊息
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => '資料庫連線失敗：' . $e->getMessage()]);
    exit; // 終止程式
}
?>