<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搶票系統 - 選擇票券區域</title>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const concertId = new URLSearchParams(window.location.search).get('concert_id');
            const concertTitle = new URLSearchParams(window.location.search).get('concert-title');
            const dateSelect = document.getElementById('date-select');
            const zoneSelect = document.getElementById('zone-select');
            const confirmBtn = document.getElementById('confirm-btn');

            // 替換活動標題
            if (concertTitle) {
                document.getElementById('event_name').innerText = decodeURIComponent(concertTitle);
            } else {
                document.getElementById('event_name').innerText = "演唱會購票";
            }
            
            // 1. 先抓取該演唱會的所有可用日期
            fetch(`get_dates.php?concert_id=${concertId}`)
            .then(res => res.json())
            .then(result => {
                result.dates.forEach(date => {
                    const opt = document.createElement('option');
                    opt.value = date;
                    opt.text = date;
                    dateSelect.appendChild(opt);
                });
            });

            // 2. 當日期變更時，抓取該日期的票區
            dateSelect.addEventListener('change', function() {
                const selectedDate = this.value;
                if (!selectedDate) {
                    zoneSelect.disabled = true;
                    confirmBtn.disabled = true;
                    confirmBtn.innerText = "請先選擇區域";
                    return;
                }
                
                fetch(`get_zones.php?concert_id=${concertId}&date=${selectedDate}`)
                .then(res => res.json())
                .then(result => {
                    zoneSelect.innerHTML = '<option value="">-- 請選擇區域 --</option>';
                    result.data.forEach(zone => {
                        const opt = document.createElement('option');
                        opt.value = zone.zone_id;
                        opt.text = `${zone.zone_name} - $${zone.price}`;
                        zoneSelect.appendChild(opt);
                    });
                    zoneSelect.disabled = false;
                });
            });

            // 3. 當票區下拉選單改變時，啟用或停用確認按鈕
            zoneSelect.addEventListener('change', function() {
                if (this.value) {
                    // 啟用按鈕
                    confirmBtn.disabled = false;
                    confirmBtn.innerText = "立即前往結帳";
                    
                    // 綁定點擊動作
                    confirmBtn.onclick = function() {
                        goToCheckout(concertId, zoneSelect.value);
                    };
                } else {
                    // 若選單變回預設值，就重設按鈕
                    confirmBtn.disabled = true;
                    confirmBtn.innerText = "請先選擇區域";
                }
            });
        });

        // 點選票區後的動作（跳轉到 checkout.php）
        function goToCheckout(concertId, zoneId) {
            window.location.href = `checkout.php?concert_id=${concertId}&zone_id=${zoneId}`;
        }
    </script>

    <div class="container">
        <h1 id="event_name">...</h1>

        <h2>📅 請選擇演出場次</h2>
        <select id="date-select" style="width: 100%; padding: 15px; font-size: 1.1rem; border-radius: 8px;">
            <option value="">-- 請先選擇日期 --</option>
        </select>
        
        <h2>🎟️ 請選擇票券區域</h2>
        <select id="zone-select" style="width: 100%; padding: 15px; font-size: 1.1rem; border-radius: 8px;" disabled>
            <option value="">-- 請選擇區域 --</option>
        </select>

        <div style="margin-top: 20px;">
            <button id="confirm-btn" class="btn-go" style="width: 100%;" disabled>請先選擇區域</button>
        </div>
    </div>
</body>
</html>