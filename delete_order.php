<?php
// delete_order.php
session_start();
require_once 'db_connect.php';

// 1. 檢查登入狀態
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$order_no = isset($_GET['order_no']) ? $_GET['order_no'] : '';

if (empty($order_no)) {
    die("無效的訂單編號");
}

try {
    // 啟動交易，確保主檔跟明細都有刪乾淨
    $pdo->beginTransaction();

    // 2. 💡 檢查這筆訂單到底是不是這個登入使用者的（對照你 orders 表的 identity_id）
    $stmtCheck = $pdo->prepare("SELECT order_no FROM orders WHERE order_no = :order_no AND identity_id = :user_id");
    $stmtCheck->execute([
        ':order_no' => $order_no,
        ':user_id'  => $user_id
    ]);
    
    if (!$stmtCheck->fetch()) {
        die("找不到此訂單");
    }

    // 3. 💡 直接下 Query：先刪除明細檔 (order_items)，再刪除主檔 (orders)
    // (如果你的資料庫有關聯設定 ON DELETE CASCADE，甚至只要寫下面那一條就好，安全起見兩條都寫)
    $stmtDelItems = $pdo->prepare("DELETE FROM order_items WHERE order_no = :order_no");
    $stmtDelItems->execute([':order_no' => $order_no]);

    $stmtDelOrder = $pdo->prepare("DELETE FROM orders WHERE order_no = :order_no");
    $stmtDelOrder->execute([':order_no' => $order_no]);

    // 提交變更
    $pdo->commit();

    // 4. 成功後直接閃人，跳回列表頁並帶上 msg 參數讓前端跳出綠色成功框
    header("Location: my_orders.php?msg=deleted");
    exit;

} catch (Exception $e) {
    // 失敗就復原
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // 噴出簡單好懂的 Alert 視窗並退回上一頁
    $safe_message = json_encode("刪除失敗：" . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    echo "<script>alert({$safe_message}); window.history.back();</script>";
    exit;
}
?>