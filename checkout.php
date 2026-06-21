<?php
session_start();
// 1. 檢查是否登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

// 2. 直接獲取網址傳過來的變數
$concert_id = isset($_GET['concert_id']) ? intval($_GET['concert_id']) : 0;
$zone_id = isset($_GET['zone_id']) ? intval($_GET['zone_id']) : 0;
$ticket_qty = isset($_GET['ticket_qty']) ? intval($_GET['ticket_qty']) : 1;
$merch_data_str = isset($_GET['merch_data']) ? $_GET['merch_data'] : '';

// 3. 直接從網址接收「活動名稱」與「票區文字」
$concert_name = isset($_GET['concert_title']) ? $_GET['concert_title'] : '未知活動';
$zone_name = isset($_GET['zone_text']) ? $_GET['zone_text'] : '未知票區';

// 💡 核心功能：從票區文字（例如："特A區 - $3200"）中自動拆出單價數字
$ticket_price = 0;
if (preg_match('/\$([0-9]+)/', $zone_name, $matches)) {
    $ticket_price = intval($matches[1]);
}
// 計算票券總計
$ticket_total = $ticket_price * $ticket_qty;

// 4. 解析周邊商品 JSON
$merch_list = [];
$merch_total = 0; // 用來累加周邊商品的總金額
if (!empty($merch_data_str)) {
    $merch_list = json_decode($merch_data_str, true);
    
    // 事先計算周邊商品總額
    if (is_array($merch_list)) {
        foreach ($merch_list as $item) {
            $merch_total += intval($item['price']) * intval($item['qty']);
        }
    }
}

// 💡 最終訂單總金額 = 票券總計 + 周邊總計
$grand_total = $ticket_total + $merch_total;
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>確認訂單 - 搶票系統</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        h3 { color: #34495e; font-size: 1.1rem; margin-top: 25px; margin-bottom: 10px; }
        
        .order-info-list { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin: 15px 0; line-height: 1.8; }
        .item-row { display: flex; justify-content: space-between; }
        
        .merch-review-list { background-color: #fcfcfc; border: 1px dashed #bdc3c7; border-radius: 6px; padding: 15px; margin: 15px 0; }
        .merch-review-item { display: flex; justify-content: space-between; margin-bottom: 8px; color: #4a5568; }
        .merch-review-item:last-child { margin-bottom: 0; }
        
        /* 總金額外觀樣式 */
        .total-section { background-color: #fff5f5; border: 2px solid #feb2b2; border-radius: 8px; padding: 20px; margin-top: 25px; text-align: right; }
        .total-price { font-size: 1.8rem; color: #e74c3c; font-weight: bold; margin-left: 10px; }
        
        .btn-submit { width: 100%; background-color: #e74c3c; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 20px; font-size: 1.2rem; transition: background 0.2s; }
        .btn-submit:hover { background-color: #c0392b; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛒 訂單確認</h2>
    <p>會員名稱：<strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
    
    <h3>🎟️ 票券商品明細</h3>
    <div class="order-info-list">
        <div>活動名稱: <span style="font-weight: bold;"><?php echo htmlspecialchars($concert_name); ?></span></div>
        <div>選擇票區: <span style="font-weight: bold;"><?php echo htmlspecialchars($zone_name); ?></span></div>
        <div class="item-row">
            <span>購買數量: <span style="font-weight: bold;"><?php echo $ticket_qty; ?> 張</span></span>
            <strong>小計: $<?php echo number_format($ticket_total); ?></strong>
        </div>
    </div>

    <?php if (is_array($merch_list) && count($merch_list) > 0): ?>
    <h3>🛍️ 加購周邊商品明細</h3>
    <div class="merch-review-list">
        <?php foreach ($merch_list as $merch_id => $item): ?>
            <div class="merch-review-item">
                <span>加購商品: <strong><?php echo htmlspecialchars($item['name']); ?></strong></span>
                <strong>
                    <?php echo htmlspecialchars($item['qty']); ?> 件 
                    (單價: $<?php echo htmlspecialchars($item['price']); ?> / 
                    小計: $<?php echo number_format(intval($item['price']) * intval($item['qty'])); ?>)
                </strong>
            </div>
        <?php endforeach; ?>
        <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 10px 0;">
        <div class="item-row" style="color: #4a5568;">
            <span>周邊商品項目總計</span>
            <strong>$<?php echo number_format($merch_total); ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <div class="total-section">
        <div style="font-size: 0.9rem; margin-bottom: 5px;">
            🏪 付款方式：<strong>當日現場現金付款取票</strong>
        </div>
        <span>應付總金額 (TWD)：</span>
        <span class="total-price">$<?php echo number_format($grand_total); ?></span>
    </div>

    <form action="process_order.php" method="POST" id="checkout-form">
        <input type="hidden" name="concert_id" value="<?php echo $concert_id; ?>">
        <input type="hidden" name="zone_id" value="<?php echo $zone_id; ?>">
        <input type="hidden" name="ticket_qty" value="<?php echo $ticket_qty; ?>">
        <input type="hidden" name="merch_data" value="<?php echo htmlspecialchars(is_array($merch_list) ? json_encode($merch_list) : $merch_data_str); ?>">
        <input type="hidden" name="total_amount" value="<?php echo $grand_total; ?>">
        
        <button type="submit" class="btn-submit">確認付款下單</button>
    </form>
</div>

</body>
</html>