<?php
// 1. 設定回傳格式為 JSON，並採用 UTF-8 編碼
header('Content-Type: application/json; charset=utf-8');

// 2. 引入您的資料庫連接設定檔
require 'db_connect.php';

// 3. 接收並清理從前端透過 POST 傳過來的表單數據
$zone_id     = isset($_POST['zone_id']) ? trim($_POST['zone_id']) : '';
$identity_no = isset($_POST['identity_no']) ? trim($_POST['identity_no']) : '';

// 4. 基礎防錯驗證：確保參數都有正確傳入
if ($zone_id <= 0 || empty($identity_no)) {
    echo json_encode([
        'status' => 'error',
        'message' => '缺少必要的驗證參數（區域 ID 或身分證字號）'
    ]);
    exit;
}

try {
    // 5. 執行資料庫查詢
    // 💡 既然只管 zone_id，可以直接查 order_items 即可，不需 JOIN orders
    $sql = "SELECT COUNT(*) 
            FROM order_items
            WHERE zone_id = :zone_id 
              AND attendee_identity_no = :identity_no";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':zone_id'     => $zone_id,
        ':identity_no' => $identity_no
    ]);
    
    // 撈出計數結果
    $ticket_count = $stmt->fetchColumn();

    // 6. 依據查詢結果，回傳對應的 JSON 狀態給前端 JavaScript
    if ($ticket_count > 0) {
        // 資料庫已有紀錄，代表該身分證已經買過這個區域了
        echo json_encode([
            'status'  => 'exists',
            'message' => '❌ 此身分證在此區域已有購票紀錄！'
        ]);
    } else {
        // 沒有紀錄，放行
        echo json_encode([
            'status'  => 'not_exists',
            'message' => '驗證通過'
        ]);
    }

} catch (PDOException $e) {
    // 7. 捕捉資料庫錯誤
    echo json_encode([
        'status'  => 'error',
        'message' => '資料庫查詢失敗，請聯繫系統管理員。'
    ]);
}
?>