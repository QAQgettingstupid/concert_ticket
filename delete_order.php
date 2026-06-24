<?php


session_start();
require 'db_connect.php';
require 'OrderDAO.php';  // 引入第三層

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
    $pdo->beginTransaction();

    // 呼叫 DAO，自己不寫 SQL
    $order = getOrderByNo($pdo, $order_no, $user_id);
    if (!$order) {
        die("找不到此訂單或您無權刪除");
    }

    deleteOrder($pdo, $order_no);

    $pdo->commit();
    header("Location: my_orders.php?msg=deleted");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $safe_message = json_encode("刪除失敗：" . $e->getMessage(), JSON_UNESCAPED_UNICODE);
    echo "<script>alert({$safe_message}); window.history.back();</script>";
    exit;
}
?>
