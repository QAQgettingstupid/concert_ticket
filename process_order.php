<?php
session_start();
// 1. 檢查登入與基本安全檢查
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 假設你已經有一個 db_connect.php 用來連線資料庫 $pdo
require 'db_connect.php'; 

// 2. 接收從 checkout.php POST 過來的資料
$user_id       = $_SESSION['user_id'];
$concert_id    = isset($_POST['concert_id']) ? intval($_POST['concert_id']) : 0;
$zone_id       = isset($_POST['zone_id']) ? $_POST['zone_id'] : 0;
$ticket_qty    = isset($_POST['ticket_qty']) ? intval($_POST['ticket_qty']) : 0;
$ticket_price  = isset($_POST['ticket_price']) ? intval($_POST['ticket_price']) : 0;
$merch_data_str= isset($_POST['merch_data']) ? $_POST['merch_data'] : '';
$total_amount  = isset($_POST['total_amount']) ? intval($_POST['total_amount']) : 0;
$attendee_data_str = isset($_POST['attendee_data']) ? $_POST['attendee_data'] : '';


$merch_list = json_decode($merch_data_str, true);
$attendee_list = json_decode($attendee_data_str, true);

function isValidTaiwanID(string $id): bool {
    $id = strtoupper(trim($id));
    
    // ① 格式檢查：1個大寫英文 + 9個數字
    if (!preg_match('/^[A-Z][0-9]{9}$/', $id)) {
        return false;
    }

    // ② 第一碼英文字母轉換對照表
    $letter_map = [
        'A'=>10,'B'=>11,'C'=>12,'D'=>13,'E'=>14,'F'=>15,'G'=>16,'H'=>17,
        'I'=>34,'J'=>18,'K'=>19,'L'=>20,'M'=>21,'N'=>22,'O'=>35,'P'=>23,
        'Q'=>24,'R'=>25,'S'=>26,'T'=>27,'U'=>28,'V'=>29,'W'=>32,'X'=>30,
        'Y'=>31,'Z'=>33
    ];

    $n = $letter_map[$id[0]];

    // ③ 檢查碼計算
    // 英文字母轉成兩位數，十位數 × 1，個位數 × 9
    $total = intval($n / 10) * 1 + ($n % 10) * 9;

    // 第2到第9碼，各自乘以權重 8,7,6,5,4,3,2,1
    $weights = [8, 7, 6, 5, 4, 3, 2, 1];
    for ($i = 1; $i <= 8; $i++) {
        $total += intval($id[$i]) * $weights[$i - 1];
    }

    // 最後一碼（第10碼）直接加入
    $total += intval($id[9]);

    // ④ 總和必須能被 10 整除才是合法身分證
    return $total % 10 === 0;
}

if (!is_array($attendee_list) || count($attendee_list) === 0) {
    die("<script>alert('請填寫入場人實名資料'); window.history.back();</script>");
}

foreach ($attendee_list as $person) {
    $name      = trim($person['name'] ?? '');
    $id_number = strtoupper(trim($person['id_number'] ?? ''));
    $index     = intval($person['index']);

    // ① 姓名不能空白
    if (empty($name)) {
        $msg = json_encode("第 {$index} 位入場人姓名不能空白", JSON_UNESCAPED_UNICODE);
        die("<script>alert({$msg}); window.history.back();</script>");
    }

    // ② 身分證格式檢查
    if (!isValidTaiwanID($id_number)) {
        $msg = json_encode("第 {$index} 位（{$name}）身分證格式錯誤：{$id_number}\n正確格式：1個大寫英文 + 9個數字，例如 A123456789", JSON_UNESCAPED_UNICODE);
        die("<script>alert({$msg}); window.history.back();</script>");
    }

    // ③ 檢查此身分證是否已買過同場演唱會
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) 
        FROM order_items oi
        JOIN orders o      ON oi.order_no  = o.order_no
        JOIN ticket_zones tz ON oi.zone_id = tz.zone_id
        JOIN event_dates ed  ON tz.date_id = ed.date_id
        WHERE oi.attendee_identity_no = ?
          AND ed.event_id   = ?
          AND oi.item_type  = 'Ticket'
          AND o.status     != '已逾期'
    ");
    $stmtCheck->execute([$id_number, $concert_id]);

    if ($stmtCheck->fetchColumn() > 0) {
        $msg = json_encode("身分證 {$id_number}（{$name}）已購買過此演唱會門票\n每人每場限購一次，無法重複購買", JSON_UNESCAPED_UNICODE);
        die("<script>alert({$msg}); window.history.back();</script>");
    }
}



echo $zone_id."<br>";
foreach ($merch_list as $merch_id => $item) {
    if (intval($item['qty']) > 0) {
        echo "Merchandise ID: $merch_id, Name: {$item['name']}, Price: {$item['price']}, Quantity: {$item['qty']}<br>";
    }
}

if ($concert_id === 0 || $zone_id === 0 || $ticket_qty <= 0) {
    die("無效的訂單資料。");
}

try {
   
    // 💡 核心安全機制開始：啟動資料庫交易 (Transaction)
    // 這樣可以確保「檢查庫存 -> 扣除庫存 -> 寫入訂單」這整串動作是原子性的（一失敗就全部不算數）
    $pdo->beginTransaction();
    
    // ==========================================
    // 步驟 A：檢查並扣除「門票庫存」
    // ==========================================
    // 注意：加上 FOR UPDATE 是為了防範搶票時的 Concurrency (高並發) 衝突，鎖定該列資料
    $stmtZone = $pdo->prepare("SELECT available_seats FROM ticket_zones WHERE zone_id = :zone_id FOR UPDATE");
    $stmtZone->execute([':zone_id' => $zone_id]);
    $zone = $stmtZone->fetch(PDO::FETCH_ASSOC);
    if (!$zone || $zone['available_seats'] < $ticket_qty) {
        // 票不夠了！拋出例外，觸發中止
        throw new Exception("抱歉，您選擇的票區剩餘座位不足，已被搶購一空！");
    }

    // 庫存夠，扣除門票庫存
    $updateZone = $pdo->prepare("UPDATE ticket_zones SET available_seats = available_seats - :qty WHERE zone_id = :zone_id");
    $updateZone->execute([':qty' => $ticket_qty, ':zone_id' => $zone_id]);

    // ==========================================
    // 步驟 B：檢查並扣除「周邊商品庫存」
    // ==========================================
    if (is_array($merch_list) && count($merch_list) > 0) {
        foreach ($merch_list as $merch_id => $item) {
            $qty = intval($item['qty']);
            if ($qty <= 0) continue; // 沒加購的就跳過

            // 檢查該周邊商品的資料庫剩餘庫存
            $stmtMerch = $pdo->prepare("SELECT stock, prod_name FROM merchandises WHERE merchandise_id = :id FOR UPDATE");
            $stmtMerch->execute([':id' => $merch_id]);
            $merch = $stmtMerch->fetch(PDO::FETCH_ASSOC);

            if (!$merch || $merch['stock'] < $qty) {
                // 周邊商品不夠！拋出例外
                throw new Exception("抱歉，加購商品【" . $merch['prod_name'] . "】庫存不足！");
            }

            // 庫存夠，扣除周邊商品庫存
            $updateMerch = $pdo->prepare("UPDATE merchandises SET stock = stock - :qty WHERE merchandise_id = :id");
            $updateMerch->execute([':qty' => $qty, ':id' => $merch_id]);
        }
    }

    // ==========================================
    // 步驟 C：寫入主訂單資料表 (orders)
    // ==========================================

    // 手動生成訂單 order_no
    $new_order_id = date('YmdHis') . rand(1000, 9999); 

    $stmtOrder = $pdo->prepare("INSERT INTO orders (order_no, identity_id, total_amount, status, created_at) 
                                VALUES (:order_no, :user_id, :total_amount, '未付款', NOW())");
    $stmtOrder->execute([
        ':order_no'     => $new_order_id,
        ':user_id'      => $user_id,
        ':total_amount' => $total_amount
    ]);

     echo "好欸<br>";

    // ==========================================
    // 步驟 D：寫入訂單明細表 (order_items)
    // ==========================================
    
    
    // 寫入周邊商品明細
    if (is_array($merch_list) && count($merch_list) > 0) {
        $stmtMerch = $pdo->prepare("INSERT INTO order_items (order_no, zone_id, merchandise_id, item_type, quantity, unit_price, attendee_name, attendee_identity_no) 
                                    VALUES (:order_id, NULL, :merch_id, 'merchandise', :qty, :price, NULL, NULL)");
        
        foreach ($merch_list as $merch_id => $item) {
            if (intval($item['qty']) <= 0) continue;
            
            $stmtMerch->execute([
                ':order_id' => $new_order_id,
                ':merch_id' => $merch_id,
                ':qty'      => $item['qty'],
                ':price'    => $item['price']
            ]);
        }
    }

    // 寫入票劵明細
    if (is_array($attendee_list) && count($attendee_list) > 0) {
        $stmtTicket = $pdo->prepare("INSERT INTO order_items (order_no, zone_id, merchandise_id, item_type, quantity, unit_price, attendee_name, attendee_identity_no) 
                                    VALUES (:order_id, :zone_id, NULL, 'Ticket', 1, :price, :attendee_name, :attendee_id)");

        // 依據入場人數量（也就是票數），一筆一筆分別寫入！
        foreach ($attendee_list as $person) {
            if (empty($person['name']) || empty($person['id_number'])) continue;

            $stmtTicket->execute([
                ':order_id'      => $new_order_id,
                ':price'         => $ticket_price,  
                ':zone_id'       => $zone_id,                   
                ':attendee_name' => $person['name'],
                ':attendee_id'   => $person['id_number']
            ]);
        }
    }



    // 💡 萬事具備，正式提交確認！此時才會真正寫入資料庫變更
    $pdo->commit();

    echo "以扣除數量<br>";


    // 下單成功，導向成功頁面，順便把訂單編號跟總金額傳過去顯示
    header("Location: order_success.php?order_id=" . $new_order_id . "&total=" . $total_amount);
    exit;

} catch (Exception $e) {
    // 💡 只要上面有任何地方 throw 失敗，就會一秒觸發 rollback
    // 剛剛扣掉的票、扣掉的周邊、寫一半的訂單，通通會倒帶回復原狀，完全不怕髒資料！
    $pdo->rollBack();

    // 💡 修正：使用 json_encode 把錯誤訊息轉成安全、有自動跳脫的 JS 字串
    $safe_message = json_encode($e->getMessage(), JSON_UNESCAPED_UNICODE);

    // 用 JavaScript 跳出失敗視窗，並退回上一頁
    echo "<script>
            alert({$safe_message});
            window.history.back();
          </script>";
    exit;
}
