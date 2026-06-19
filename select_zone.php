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
    </style>
</head>
<body>

    <div class="container">
        <h1 id="event-title">載入中...</h1>
        <div class="concert-meta">📍 地點：台北小巨蛋 | 📅 日期：2026-12-31</div>

        <h2>🎟️ 請選擇票券區域</h2>
        <div id="zone-list"></div>
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
                        // 更新頁面大標題
                        document.getElementById('event-title').innerText = result.event_title;

                        const zoneContainer = document.getElementById('zone-list');
                        zoneContainer.innerHTML = '';

                        // 3. 迴圈渲染每一個票區
                        result.data.forEach(zone => {
                            const item = document.createElement('div');
                            item.className = 'zone-item';

                            // 模擬座位如果小於等於 0 就停用按鈕
                            let actionButton = '';
                            if (zone.available_seats > 0) {
                                actionButton = `<button class="btn-go" onclick="goToCheckout(${concertId}, ${zone.zone_id})">立即點選</button>`;
                            } else {
                                actionButton = `<button class="btn-go btn-disabled" disabled>已售罄</button>`;
                            }

                            item.innerHTML = `
                                <div class="zone-details">
                                    <h3>${zone.zone_name}</h3>
                                    <p>剩餘座位：<strong>${zone.available_seats}</strong> 位</p>
                                </div>
                                <div>
                                    <span class="price">$${zone.price}</span>
                                    ${actionButton}
                                </div>
                            `;
                            zoneContainer.appendChild(item);
                        });
                    } else {
                        document.getElementById('zone-list').innerHTML = `<p style="color:red;">${result.message}</p>`;
                    }
                })
                .catch(error => console.error('發生錯誤:', error));
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