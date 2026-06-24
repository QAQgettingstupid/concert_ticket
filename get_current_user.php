<?php
// get_current_user.php
// 1. 設定回傳格式為 JSON，並採用 UTF-8 編碼
header('Content-Type: application/json; charset=utf-8');

session_start();
require 'db_connect.php';

// 2. 檢查使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => '登入已逾期或未登入'
    ]);
    exit;
}

try {
    // 3. 撈出目前登入者的姓名和身分證
    $stmtUser = $pdo->prepare("SELECT name, identity_id FROM users WHERE identity_id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $current_user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($current_user) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'name' => $current_user['name'],
                'identity_id' => $current_user['identity_id']
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => '找不到該會員的帳號資料'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => '資料庫連線異常，請稍後再試。'
    ]);
}