<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搶票系統 - 選擇票券區域</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; background-color: #f8f9fa; color: #333; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; margin-top: 0; }
        
        select, input[type="text"] {
            border: 2px solid #3498db;
            background-color: white;
            width: 100%; 
            padding: 15px; 
            font-size: 1.1rem; 
            border-radius: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #2980b9;
        }
        
        .hint-text { color: #e74c3c; font-size: 0.9rem; margin-top: -10px; margin-bottom: 20px; font-weight: bold; }
        .merch-section { background-color: #fcfcfc; border: 1px dashed #bdc3c7; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .merch-item { margin-bottom: 15px; }
        .merch-item label { display: block; font-weight: bold; margin-bottom: 5px; color: #2c3e50; }
        
        /* 實名制欄位樣式 */
        .attendee-section { background-color: #edf2f7; border: 1px solid #cbd5e1; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .attendee-group { background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 5px solid #3498db; }
        .attendee-group h4 { margin: 0 0 10px 0; color: #2c3e50; }
        .attendee-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.95rem; }

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
            const attendeeContainer = document.getElementById('dynamic-attendee-container');
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
                result.dates.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.date_id;    
                    opt.text = item.event_date;  
                    dateSelect.appendChild(opt);
                });
            });

            // 3. 當日期變更時，抓取該日期的票區
            dateSelect.addEventListener('change', function() {
                const selectedDateid = this.value;
                if (!selectedDateid) {
                    resetForm();
                    return;
                }
                
                fetch(`get_zones.php?date_id=${selectedDateid}`)
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

            // 抓取該場次對應的資料庫周邊商品清單
            fetch(`get_merchandises.php?concert_id=${concertId}`)
            .then(res => res.json())
            .then(result => {
                if (result.status === 'success' && result.data.length > 0) {
                    merchContainer.innerHTML = ''; 
                    
                    result.data.forEach(merch => {
                        const div = document.createElement('div');
                        div.className = 'merch-item';
                        
                        let optionsHTML = '<option value="0">不加購</option>';
                        let isDisabledAttr = 'disabled';
                        let stockDisplay = '';

                        if (merch.stock <= 0) {
                            stockDisplay = `<span class="stock-highlight" id="stock-count-${merch.merchandise_id}">❌ 已售罄</span>`;
                        } else {
                            stockDisplay = `<span class="stock-highlight" id="stock-count-${merch.merchandise_id}">(剩餘 ${merch.stock} 個)</span>`;
                            const maxQty = Math.min(4, merch.stock);
                            for (let i = 1; i <= maxQty; i++) {
                                optionsHTML += `<option value="${i}">${i} 件</option>`;
                            }
                        }

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

                    setupMerchStockListeners();

                } else {
                    merchContainer.innerHTML = '<p style="color: #95a5a6;">本場次暫無提供周邊商品加購。</p>';
                }
            });

            // 4. 當票區選擇後，解鎖張數與周邊商品，並更新實名制欄位
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
                    
                    // 💡 新增：解鎖時依據目前的張數先產生一次實名制欄位
                    updateAttendeeFields(parseInt(ticketQtySelect.value));

                    confirmBtn.onclick = function() {
                        const zoneText = zoneSelect.options[zoneSelect.selectedIndex].text;

                        // 抓取周邊商品
                        let merchSelections = {};
                        const merchSelects = document.querySelectorAll('.merch-select-field');
                        merchSelects.forEach(select => {
                            const qty = select.value;
                            if (qty > 0) {
                                const merchId = select.getAttribute('data-id');
                                const name = select.getAttribute('data-name');
                                const price = select.getAttribute('data-price');
                                
                                merchSelections[merchId] = {
                                    qty: qty,
                                    name: name,
                                    price: price
                                };
                            }
                        });

                        // 💡 新增：抓取實名制填寫的入場人資訊
                        let attendeeData = [];
                        const groups = document.querySelectorAll('.attendee-group');
                        let isFormValid = true;

                        groups.forEach((group, index) => {
                            const nameInput = group.querySelector('.attendee-name');
                            const idInput = group.querySelector('.attendee-id');

                            // 簡單的前端防呆驗證
                            if (!nameInput.value.trim() || !idInput.value.trim()) {
                                isFormValid = false;
                            }

                            attendeeData.push({
                                index: index + 1,
                                name: nameInput.value.trim(),
                                id_number: idInput.value.trim()
                            });
                        });

                        if (!isFormValid) {
                            alert("請填寫所有入場人的姓名與身分證字號！");
                            return; // 擋下不讓跳轉
                        }

                        // 呼叫跳轉，把 attendeeData 也傳過去
                        goToCheckout(concertId, zoneSelect.value, zoneText, ticketQtySelect.value, merchSelections, attendeeData);
                    };
                } else {
                    lockSubFields();
                }
            });

            // 💡 新增：監聽購買張數變更事件，動態調整實名制欄位數量
            ticketQtySelect.addEventListener('change', function() {
                updateAttendeeFields(parseInt(this.value));
            });

            // 💡 新增：動態渲染實名制欄位的函數
            function updateAttendeeFields(qty) {
                attendeeContainer.innerHTML = ''; // 先清空舊欄位
                
                for (let i = 1; i <= qty; i++) {
                    const div = document.createElement('div');
                    div.className = 'attendee-group';
                    div.innerHTML = `
                        <h4>👤 第 ${i} 位入場人資訊</h4>
                        <label>真實姓名：</label>
                        <input type="text" class="attendee-name" placeholder="請輸入身分證上的姓名" required>
                        
                        <label>身分證字號：</label>
                        <input type="text" class="attendee-id" placeholder="請輸入身分證字號" required>
                    `;
                    attendeeContainer.appendChild(div);
                }
            }

            // 動態監聽周邊選單變更
            function setupMerchStockListeners() {
                const merchSelects = document.querySelectorAll('.merch-select-field');
                merchSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        const merchId = this.getAttribute('data-id');
                        const maxStock = parseInt(this.getAttribute('data-max-stock'));
                        const userSelectedQty = parseInt(this.value);
                        
                        const stockSpan = document.getElementById(`stock-count-${merchId}`);
                        if (stockSpan) {
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
                attendeeContainer.innerHTML = '<p style="color: #95a5a6;">請先選擇區域與張數。</p>';
            }

            function resetForm() {
                zoneSelect.innerHTML = '<option value="">-- 請選擇區域 --</option>';
                zoneSelect.disabled = true;
                ticketQtySelect.value = "1";
                document.querySelectorAll('.merch-select-field').forEach(select => {
                    select.value = "0";
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

        //  URL 變數傳遞
        function goToCheckout(concertId, zoneId, zoneText, ticketQty, merchSelections, attendeeData) {
            const merchJson = encodeURIComponent(JSON.stringify(merchSelections));
            const attendeeJson = encodeURIComponent(JSON.stringify(attendeeData)); // 打包實名制資料
            const zTextEncoded = encodeURIComponent(zoneText);
            const cTitleEncoded = encodeURIComponent(document.getElementById('event_name').innerText);

            window.location.href = `checkout.php?concert_id=${concertId}&concert_title=${cTitleEncoded}&zone_id=${zoneId}&zone_text=${zTextEncoded}&ticket_qty=${ticketQty}&merch_data=${merchJson}&attendee_data=${attendeeJson}`;
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

        <div class="attendee-section">
            <h2>📝 實名制入場人資訊</h2>
            <div id="dynamic-attendee-container">
                <p style="color: #95a5a6;">請先選擇區域與張數。</p>
            </div>
        </div>

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