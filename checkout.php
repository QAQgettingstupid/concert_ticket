<?php
session_start();
// 1. 檢查是否登入
if (!isset($_SESSION['user_id'])) {
    // 沒登入就記錄現在想去的網址，稍後登入完導回來 (選用)
    header("Location: login.php?redirect=checkout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>確認訂單 - 搶票系統</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; }
        .btn-submit { width: 100%; background-color: #e74c3c; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <h2>訂單確認</h2>
    <p>會員：<?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    
    <div id="order-info">
        <p>活動編號: <span id="c-id"></span></p>
        <p>票區編號: <span id="z-id"></span></p>
    </div>

    <form action="process_order.php" method="POST">
        <input type="hidden" name="concert_id" id="hidden-concert-id">
        <input type="hidden" name="zone_id" id="hidden-zone-id">
        
        <label>購買張數：</label>
        <input type="number" name="quantity" min="1" max="4" value="1" required>
        
        <button type="submit" class="btn-submit">確認付款下單</button>
    </form>
</div>

<script>
    // 從網址讀取參數並顯示
    const params = new URLSearchParams(window.location.search);
    document.getElementById('c-id').innerText = params.get('concert_id');
    document.getElementById('z-id').innerText = params.get('zone_id');
    
    // 填入隱藏欄位供送出訂單時使用
    document.getElementById('hidden-concert-id').value = params.get('concert_id');
    document.getElementById('hidden-zone-id').value = params.get('zone_id');
</script>

</body>
</html>