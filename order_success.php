<?php
session_start();
// 1. 安全檢查：如果沒登入，或是網址沒有帶訂單編號，直接踢回首頁或登入頁
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: login.php");
    exit;
}

// 2. 獲取網址傳過來的訂單資訊
$order_id = intval($_GET['order_id']);
$total_amount = isset($_GET['total']) ? intval($_GET['total']) : 0;
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搶票成功 - 訂單成立</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f8f9fa; padding: 40px; color: #333; }
        .success-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; }
        
        .success-icon { font-size: 4rem; color: #2ecc71; margin-bottom: 15px; }
        h1 { color: #2c3e50; margin-top: 0; font-size: 1.8rem; }
        .sub-title { color: #7f8c8d; margin-bottom: 30px; }
        
        /* 訂單明細卡片樣式 */
        .order-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; text-align: left; margin-bottom: 25px; }
        .order-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #edf2f7; }
        .order-row:last-child { border-bottom: none; }
        .label { color: #4a5568; font-weight: 500; }
        .value { color: #1a202c; font-weight: bold; }
        .price-highlight { color: #e74c3c; font-size: 1.3rem; }
        
        /* 溫馨提示區塊 */
        .notice-box { background-color: #fffaf0; border: 1px solid #feebc8; border-radius: 8px; padding: 20px; text-align: left; color: #c05621; font-size: 0.95rem; margin-bottom: 30px; line-height: 1.6; }
        .notice-title { font-weight: bold; margin-bottom: 5px; display: flex; align-items: center; }
        
        /* 按鈕樣式 */
        .btn-group { display: flex; gap: 15px; justify-content: center; }
        .btn { padding: 12px 25px; border-radius: 6px; font-weight: bold; text-decoration: none; cursor: pointer; display: inline-block; transition: background 0.2s; }
        .btn-primary { background-color: #3498db; color: white; border: none; }
        .btn-primary:hover { background-color: #2980b9; }
        .btn-secondary { background-color: #95a5a6; color: white; border: none; }
        .btn-secondary:hover { background-color: #7f8c8d; }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-icon">🎉</div>
    <h1>恭喜您，搶票成功！</h1>
    <p class="sub-title">您的訂單已經成功建立，系統已為您保留名額與加購商品。</p>

    <div class="order-card">
        <div class="order-row">
            <span class="label">訂單編號：</span>
            <span class="value">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        <div class="order-row">
            <span class="label">購買會員：</span>
            <span class="value"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        </div>
        <div class="order-row">
            <span class="label">應付總金額：</span>
            <span class="value price-highlight">$<?php echo number_format($total_amount); ?> TWD</span>
        </div>
        <div class="order-row">
            <span class="label">付款狀態：</span>
            <span class="value" style="color: #d69e2e;">⏳ 現場待付款</span>
        </div>
    </div>

    <div class="notice-box">
        <div class="notice-title">💡 現場取票與付款須知：</div>
        1. 演出當天請至活動現場的「網路購票取票處」，向工作人員出示您的<strong>訂單編號</strong>。<br>
        2. 請務必攜帶與<strong>入場人</strong>相符的<strong>身分證件</strong>供核對身份。<br>
    </div>

    <div class="btn-group">
        <a href="my_orders.php" class="btn btn-primary">查看我的訂單</a>
        <a href="home.php" class="btn btn-secondary">回首頁</a>
    </div>
</div>

</body>
</html>