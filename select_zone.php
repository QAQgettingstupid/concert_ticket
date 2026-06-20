<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搶票系統 - 選擇票券區域 (呈現層)</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; background-color: #f8f9fa; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; margin-top: 0; }
        .concert-meta { color: #7f8c8d; font-size: 1.1rem; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #ecf0f1; }
        
        /* 票區列表樣式 */
        .zone-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 20px; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            margin-bottom: 15px; 
        }
        .zone-details h3 { margin: 0 0 5px 0; color: #2d3748; }
        .zone-details p { margin: 0; color: #718096; font-size: 0.95rem; }
        .price { color: #e74c3c; font-weight: bold; font-size: 1.2rem; }
        
        /* 按鈕樣式 */
        .btn-go { background-color: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; text-decoration: none; }
        .btn-go:hover { background-color: #27ae60; }
        .btn-disabled { background-color: #cbd5e1; color: #94a3b8; cursor: not-allowed; }

        /* 下拉選單樣式 */
        select {
        border: 2px solid #3498db;
        background-color: white;
        cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 id="event-title">載入中...</h1>
        <div class="concert-meta">📍 地點：台北小巨蛋 | 📅 日期：2026-12-31</div>

        <h2>🎟️ 請選擇票券區域</h2>
        <select id="zone-select" style="width: 100%; padding: 15px; font-size: 1.1rem; border-radius: 8px;">
            <option value="">-- 請選擇區域 --</option>
        </select>

        <div style="margin-top: 20px;">
            <button id="confirm-btn" class="btn-go" style="width: 100%;" disabled>請先選擇區域</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. 從網址列抓取傳過來的 concert_id (例如 ?concert_id=5)
            const urlParams = new URLSearchParams(window.location.search);
            const concertId = urlParams.get('concert_id');

            if (!concertId) {
                alert('找不到演唱會 ID！將返回首頁');
                window.location.href = 'index.html';
                return;
            }

            // 2. 帶著網址參數呼叫後端 get_zones.php
            fetch(`get_zones.php?concert_id=${concertId}`)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    document.getElementById('event-title').innerText = result.event_title;
                    
                    const select = document.getElementById('zone-select');
                    const btn = document.getElementById('confirm-btn');

                    result.data.forEach(zone => {
                        const option = document.createElement('option');
                        option.value = zone.zone_id;
                        
                        // 如果沒票了，在選項上標記「已售罄」且設為 disabled
                        if (zone.available_seats <= 0) {
                            option.text = `${zone.zone_name} - $${zone.price} (已售罄)`;
                            option.disabled = true;
                        } else {
                            option.text = `${zone.zone_name} - $${zone.price} (剩餘: ${zone.available_seats})`;
                        }
                        select.appendChild(option);
                    });

                    // 當選單變更時，啟用按鈕並綁定動作
                    select.addEventListener('change', function() {
                        if (this.value) {
                            btn.disabled = false;
                            btn.innerText = "立即前往結帳";
                            btn.onclick = () => goToCheckout(concertId, this.value);
                        } else {
                            btn.disabled = true;
                            btn.innerText = "請先選擇區域";
                        }
                    });
                }
            });

        // 點選票區後的動作（進入第三關：填寫張數頁）
        function goToCheckout(concertId, zoneId) {
            // 把演唱會 ID 和區域 ID 一起帶去下一頁
            alert(`已選定區域！準備進入搶票結帳頁...\nConcert ID: ${concertId}, Zone ID: ${zoneId}`);
            // window.location.href = `checkout.html?concert_id=${concertId}&zone_id=${zoneId}`;
        }
    </script>
</body>
</html>