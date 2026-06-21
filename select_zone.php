<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搶票系統 - 選擇票券區域</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; background-color: #f8f9fa; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; margin-top: 0; }
        
        select {
            border: 2px solid #3498db;
            background-color: white;
            cursor: pointer;
            width: 100%; 
            padding: 15px; 
            font-size: 1.1rem; 
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .hint-text { color: #e74c3c; font-size: 0.9rem; margin-top: -10px; margin-bottom: 20px; font-weight: bold; }
        .merch-section { background-color: #fcfcfc; border: 1px dashed #bdc3c7; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .merch-item { margin-bottom: 15px; }
        .merch-item label { display: block; font-weight: bold; margin-bottom: 5px; color: #2c3e50; }
        
        /* 新增：紅字標記剩餘數量的樣式 */
        .stock-highlight {
            color: #db594b;
            font-weight: bold;
            margin-left: 10px;
            font-size: 0.9rem;
        }

        .btn-go { background-color: #2ecc71; color: white; border: none; padding: 15px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; text-decoration: none; }
        .btn-go:hover { background-color: #27ae60; }
        .btn-go:disabled { background-color: #cbd5e1; color: #94a3b8; cursor: not-allowed; }
    </style>
</head>
<body>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const concertId = new URLSearchParams(window.location.search).get('concert_id');
            const concertTitle = new URLSearchParams(window.location.search).get('concert-title');
            
            const dateSelect = document.getElementById('date-select');
            const zoneSelect = document.getElementById('zone-select');
            const ticketQtySelect = document.getElementById('ticket-qty');
            const merchContainer = document.getElementById('dynamic-merch-container');
            const confirmBtn = document.getElementById('confirm-btn');

            if (concertTitle) {
                document.getElementById('event_name').innerText = decodeURIComponent(concertTitle);
            } else {
                document.getElementById('event_name').innerText = "演唱會購票";
            }
            
            // 1. 抓取可用日期
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

            // 2. 抓取該場次對應的資料庫周邊商品清單 (包含剩餘數量 stock)
            fetch(`get_merchandises.php?concert_id=${concertId}`)
            .then(res => res.json())
            .then(result => {
                if (result.status === 'success' && result.data.length > 0) {
                    merchContainer.innerHTML = ''; // 清空提示文字
                    
                    result.data.forEach(merch => {
                        const div = document.createElement('div');
                        div.className = 'merch-item';
                        
                        let optionsHTML = '<option value="0">不加購</option>';
                        let isDisabledAttr = 'disabled';
                        let stockDisplay = '';

                        // 判斷庫存
                        if (merch.stock <= 0) {
                            stockDisplay = `<span class="stock-highlight" id="stock-count-${merch.merchandise_id}">❌ 已售罄</span>`;
                        } else {
                            // 初始顯示原始庫存
                            stockDisplay = `<span class="stock-highlight" id="stock-count-${merch.merchandise_id}">(剩餘 ${merch.stock} 個)</span>`;
                            
                            // 計算最高購買數上限 4
                            const maxQty = Math.min(4, merch.stock);
                            for (let i = 1; i <= maxQty; i++) {
                                optionsHTML += `<option value="${i}">${i} 件</option>`;
                            }
                        }

                        // 將剩餘數量直接嵌入到 label 右邊
                        div.innerHTML = `
                            <label>
                                ${merch.prod_name} ($${parseInt(merch.price)})：
                                ${stockDisplay}
                            </label>
                            <select class="merch-select-field" 
                                    data-id="${merch.merchandise_id}" 
                                    data-max-stock="${merch.stock}" 
                                    data-name="${merch.prod_name}" 
                                    data-price="${merch.price}" 
                                    ${isDisabledAttr}>
                                ${optionsHTML}
                            </select>
                        `;
                        merchContainer.appendChild(div);
                    });

                    // 【核心加強】幫所有剛生成的周邊選單加上動態刷新庫存事件
                    setupMerchStockListeners();

                } else {
                    merchContainer.innerHTML = '<p style="color: #95a5a6;">本場次暫無提供周邊商品加購。</p>';
                }
            });

            // 3. 當日期變更時，抓取該日期的票區
            dateSelect.addEventListener('change', function() {
                const selectedDate = this.value;
                if (!selectedDate) {
                    resetForm();
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

            // 4. 當票區選擇後，解鎖張數與周邊商品
            zoneSelect.addEventListener('change', function() {
                const merchSelects = document.querySelectorAll('.merch-select-field');
                
                if (this.value) {
                    ticketQtySelect.disabled = false;
                    
                    merchSelects.forEach(select => {
                        const maxStock = parseInt(select.getAttribute('data-max-stock'));
                        if (maxStock > 0) {
                            select.disabled = false;
                        }
                    });

                    confirmBtn.disabled = false;
                    confirmBtn.innerText = "立即前往結帳";
                    
                    confirmBtn.onclick = function() {
                    // 1. 額外抓取票區的「文字名稱」（例如："特A區 - $3200"）
                    const zoneText = zoneSelect.options[zoneSelect.selectedIndex].text;

                    // 2. 抓取周邊商品的詳細資訊
                    let merchSelections = {};
                    const merchSelects = document.querySelectorAll('.merch-select-field');
                    
                    merchSelects.forEach(select => {
                        const qty = select.value;
                        if (qty > 0) {
                            const merchId = select.getAttribute('data-id');
                            // 往上找到當前商品的 label 標籤，把商品名稱拿出來（或在後端生成時直接存在 data 屬性）
                            // 這裡最安全的方法是直接從畫面的 label 或自訂屬性拿。
                            // 為了方便，我們可以在生成 HTML 時，把 name 和 price 也存成 data-name 和 data-price
                            const name = select.getAttribute('data-name');
                            const price = select.getAttribute('data-price');
                            
                            merchSelections[merchId] = {
                                qty: qty,
                                name: name,
                                price: price
                            };
                        }
                    });

                    // 呼叫跳轉，把 zone_text 也當作網址變數傳過去
                    goToCheckout(concertId, zoneSelect.value, zoneText, ticketQtySelect.value, merchSelections);
                };
                } else {
                    lockSubFields();
                }
            });

            // 動態監聽周邊選單變更，隨選取數量扣除畫面的庫存
            function setupMerchStockListeners() {
                const merchSelects = document.querySelectorAll('.merch-select-field');
                merchSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        const merchId = this.getAttribute('data-id');
                        const maxStock = parseInt(this.getAttribute('data-max-stock'));
                        const userSelectedQty = parseInt(this.value);
                        
                        // 尋找對應的紅字顯示區
                        const stockSpan = document.getElementById(`stock-count-${merchId}`);
                        if (stockSpan) {
                            // 動態計算：最大庫存 - 會員目前選取的數量
                            const updatedStock = maxStock - userSelectedQty;
                            stockSpan.innerText = `剩餘 ${updatedStock} 個`;
                        }
                    });
                });
            }

            function lockSubFields() {
                ticketQtySelect.disabled = true;
                document.querySelectorAll('.merch-select-field').forEach(select => select.disabled = true);
                confirmBtn.disabled = true;
                confirmBtn.innerText = "請先選擇區域";
            }

            function resetForm() {
                zoneSelect.innerHTML = '<option value="">-- 請選擇區域 --</option>';
                zoneSelect.disabled = true;
                ticketQtySelect.value = "1";
                document.querySelectorAll('.merch-select-field').forEach(select => {
                    select.value = "0";
                    // 重設選單時，把畫面文字也改回最初的最大庫存
                    const merchId = select.getAttribute('data-id');
                    const maxStock = select.getAttribute('data-max-stock');
                    const stockSpan = document.getElementById(`stock-count-${merchId}`);
                    if (stockSpan && maxStock > 0) {
                        stockSpan.innerText = `剩餘 ${maxStock} 個`;
                    }
                });
                lockSubFields();
            }
        });

        function goToCheckout(concertId, zoneId, zoneText, ticketQty, merchSelections) {
            const merchJson = encodeURIComponent(JSON.stringify(merchSelections));
            const zTextEncoded = encodeURIComponent(zoneText);
            const cTitleEncoded = encodeURIComponent(document.getElementById('event_name').innerText);
            
            window.location.href = `checkout.php?concert_id=${concertId}&concert_title=${cTitleEncoded}&zone_id=${zoneId}&zone_text=${zTextEncoded}&ticket_qty=${ticketQty}&merch_data=${merchJson}`;
        }
    </script>

    <div class="container">
        <h1 id="event_name">...</h1>

        <h2>📅 請選擇演出場次</h2>
        <select id="date-select">
            <option value="">-- 請先選擇日期 --</option>
        </select>
        
        <h2>🎟️ 請選擇票券區域</h2>
        <select id="zone-select" disabled>
            <option value="">-- 請選擇區域 --</option>
        </select>

        <h2>👥 購買張數</h2>
        <select id="ticket-qty" disabled>
            <option value="1">1 張</option>
            <option value="2">2 張</option>
        </select>
        <div class="hint-text">⚠️ 每個帳號至多限購 2 張票券</div>

        <div class="merch-section">
            <h2>🛍️ 官方周邊商品加購</h2>
            <div id="dynamic-merch-container">
                <p style="color: #95a5a6;">正在載入本場次周邊商品...</p>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <button id="confirm-btn" class="btn-go" style="width: 100%;" disabled>請先選擇區域</button>
        </div>
    </div>
</body>
</html>