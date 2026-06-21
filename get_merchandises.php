<?php
header('Content-Type: application/json; charset=utf-8');

require 'db_connect.php';

// 1. 取得前端帶過來的 concert_id
$concert_id = isset($_GET['concert_id']) ? intval($_GET['concert_id']) : 0;

if ($concert_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => '無效的演唱會 ID']);
    exit;
}

try {

    // 3. 查詢該場次的周邊商品
    $stmt = $pdo->prepare("SELECT merchandise_id, prod_name, price, stock FROM merchandises WHERE event_id = :event_id");
    $stmt->execute(['event_id' => $concert_id]);
    $merch_list = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $merch_list]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫連線失敗: ' . $e->getMessage()]);
}