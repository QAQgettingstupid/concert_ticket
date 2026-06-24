<?php
session_start();
require 'db_connect.php';

// 撈出目前登入者的姓名和身分證
$stmtUser = $pdo->prepare("SELECT name, identity_id FROM users WHERE identity_id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$current_user = $stmtUser->fetch(PDO::FETCH_ASSOC);
?>

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
        // PHP 把登入者資料注入 JS
           const currentUserName = <?php echo json_encode($current_user['name']); ?>;
           const currentUserID   = <?php echo json_encode($current_user['identity_id']); ?>;

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

            // 2. 當日期變更時，抓取該日期的票區
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

            // 3. 抓取該場次對應的資料庫周邊商品清單
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
                    
                    updateAttendeeFields(parseInt(ticketQtySelect.value));
                } else {
                    lockSubFields();
                }
            });

            // 5. 當購買張數變更事件，動態調整實名制欄位數量
            ticketQtySelect.addEventListener('change', function() {
                updateAttendeeFields(parseInt(this.value));
            });

            // 獨立移至最外層的「立即前往結帳」點擊事件處理
            confirmBtn.onclick = async function() {
                const selectedZoneId = zoneSelect.value;
                const zoneText = zoneSelect.options[zoneSelect.selectedIndex].text;

                if (!selectedZoneId) {
                    return;
                }

                // 收集周邊商品
                let merchSelections = {};
                const merchSelects = document.querySelectorAll('.merch-select-field');
                merchSelects.forEach(select => {
                    const qty = parseInt(select.value);
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

                // 收集與驗證實名制資訊
                let attendeeData = [];
                const groups = document.querySelectorAll('.attendee-group');
                let isAllValid = true; 
                const idRegex = /^[A-Z][12]\d{8}$/; 

                for (let index = 0; index < groups.length; index++) {
                    const group = groups[index];
                    const i = index + 1;
                    const nameInput = group.querySelector('.attendee-name');
                    const idInput = group.querySelector('.attendee-id');
                    
                    const nameVal = nameInput.value.trim();
                    const idVal = idInput.value.trim().toUpperCase();

                    // 清空欄位舊錯誤狀態
                    const nameErrorDiv = document.getElementById(`name-error-msg-${i}`);
                    nameErrorDiv.innerText = '';
                    nameInput.style.borderColor = '#3498db';

                    const idErrorDiv = document.getElementById(`id-error-msg-${i}`);
                    idErrorDiv.innerText = '';
                    idInput.style.borderColor = '#3498db';

                    // 驗證姓名
                    if (!nameVal) {
                        isAllValid = false;
                        nameErrorDiv.innerText = '❌ 請輸入真實姓名（欄位不可留空）';
                        nameInput.style.borderColor = '#e74c3c';
                    }

                    // 驗證身分證格式
                    if (!idVal) {
                        isAllValid = false;
                        idErrorDiv.innerText = '❌ 請輸入身分證字號（欄位不可留空）';
                        idInput.style.borderColor = '#e74c3c';
                    } else if (!idRegex.test(idVal)) {
                        isAllValid = false;
                        idErrorDiv.innerText = '❌ 身分證字號格式錯誤（須為1碼大寫英文 + 1或2 + 8碼數字）';
                        idInput.style.borderColor = '#e74c3c';
                    }

                    attendeeData.push({
                        index: i,
                        name: nameVal,
                        id_number: idVal,
                        idInputObj: idInput,       
                        idErrorDivObj: idErrorDiv  
                    });
                }

                // 前端基本格式沒過，直接中端攔截並滾動
                if (!isAllValid) {
                    const errSection = document.querySelector('.attendee-section');
                    errSection.scrollIntoView({ behavior: 'smooth' });
                    return;
                }

                // 💡 【新增防禦】防範畫面上「同時填寫兩張票，兩張身分證輸入一模一樣」的狀況
                let idValuesArray = attendeeData.map(a => a.id_number);
                let findDuplicates = idValuesArray.filter((item, index) => idValuesArray.indexOf(item) !== index);
                if (findDuplicates.length > 0 && findDuplicates[0] !== '') {
                    attendeeData.forEach(attendee => {
                        if (attendee.id_number === findDuplicates[0]) {
                            attendee.idErrorDivObj.innerText = '❌ 同一筆訂單中，填寫的實名制身分證不可重複！';
                            attendee.idInputObj.style.borderColor = '#e74c3c';
                        }
                    });
                    const errSection = document.querySelector('.attendee-section');
                    errSection.scrollIntoView({ behavior: 'smooth' });
                    return; // 確實攔截
                }

                // 前端初檢通過，發送 POST 連線到後端 API 檢查重複購票資格
                confirmBtn.disabled = true;
                confirmBtn.innerText = "正在驗證購票資格...";

                let hasDuplicate = false; 

                try {
                    for (let attendee of attendeeData) {
                        const response = await fetch('check_identity.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'zone_id': selectedZoneId,
                                'identity_no': attendee.id_number
                            })
                        });
                        
                        // 讀取成文字檔以防後端噴非 JSON 的髒資料
                        const rawText = await response.text();
                        let checkResult;
                        
                        try {
                            checkResult = JSON.parse(rawText);
                        } catch(e) {
                            console.error("JSON 解析失敗，後端回傳：", rawText);
                            attendee.idErrorDivObj.innerText = "❌ 系統回應格式錯誤，請聯絡管理員。";
                            attendee.idInputObj.style.borderColor = '#e74c3c';
                            hasDuplicate = true;
                            continue;
                        }

                        if (checkResult.status === 'exists') {
                            // 直接在該欄位下方注入紅字訊息
                            attendee.idErrorDivObj.innerText = checkResult.message;
                            attendee.idInputObj.style.borderColor = '#e74c3c';
                            
                            if (!hasDuplicate) {
                                attendee.idInputObj.focus();
                                attendee.idInputObj.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                hasDuplicate = true;
                            }
                        } else if (checkResult.status === 'error') {
                            attendee.idErrorDivObj.innerText = `❌ ${checkResult.message}`;
                            attendee.idInputObj.style.borderColor = '#e74c3c';
                            hasDuplicate = true;
                        }
                    }

                } catch (error) {
                    console.error("驗證身分證時發生連線錯誤:", error);
                    const globalErrorDiv = document.getElementById('id-error-msg-1');
                    if (globalErrorDiv) {
                        globalErrorDiv.innerText = "❌ 系統驗證連線失敗，請稍後再試。";
                    }
                    hasDuplicate = true; // 發生連線異常，強制鎖定不給過
                }

                // ⚡ 重要修正：只要被標記為重複或錯誤，不論在哪個階段，絕對要 return 中斷，不可執行 goToCheckout
                if (hasDuplicate) {
                    confirmBtn.disabled = false;
                    confirmBtn.innerText = "立即前往結帳";
                    return; 
                }

                // 驗證全部通過，移除暫存的 DOM 物件後重定向至結帳頁面
                const cleanAttendeeData = attendeeData.map(item => ({
                    index: item.index,
                    name: item.name,
                    id_number: item.id_number
                }));

                goToCheckout(concertId, selectedZoneId, zoneText, ticketQtySelect.value, merchSelections, cleanAttendeeData);
            };

            // 動態渲染實名制欄位
            function updateAttendeeFields(qty) {
                attendeeContainer.innerHTML = ''; 
                
                for (let i = 1; i <= qty; i++) {
                    const div = document.createElement('div');
                    div.className = 'attendee-group';
                    div.innerHTML = `
                        <h4>👤 第 ${i} 位入場人資訊</h4>
                        
                        <label>真實姓名：</label>
                        <input type="text" class="attendee-name" id="name-input-${i}" placeholder="請輸入身分證上的姓名" required oninput="validateName(this, ${i})">
                        <div id="name-error-msg-${i}" style="color: #e74c3c; font-size: 0.88rem; font-weight: bold; min-height: 20px; margin-top: -5px; margin-bottom: 10px;"></div>
                        
                        <label>身分證字號：</label>
                        <input type="text" class="attendee-id" id="id-input-${i}" placeholder="請輸入身分證字號 (範例：A123456789)" required oninput="validateIdentity(this, ${i})">
                        <div id="id-error-msg-${i}" style="color: #e74c3c; font-size: 0.88rem; font-weight: bold; min-height: 20px; margin-top: -5px; margin-bottom: 10px;"></div>
                    `;
                    attendeeContainer.appendChild(div);
                }
            }

            window.validateName = function(inputElement, index) {
                const val = inputElement.value.trim();
                const errorDiv = document.getElementById(`name-error-msg-${index}`);

                if (val === '') {
                    errorDiv.innerText = '❌ 請輸入真實姓名（欄位不可留空）';
                    inputElement.style.borderColor = '#e74c3c'; 
                    return false;
                } else {
                    errorDiv.innerText = '';
                    inputElement.style.borderColor = '#3498db';
                    return true;
                }
            }

            window.validateIdentity = function(inputElement, index) {
                inputElement.value = inputElement.value.toUpperCase(); 
                const val = inputElement.value.trim();
                const errorDiv = document.getElementById(`id-error-msg-${index}`);
                const regex = /^[A-Z][12]\d{8}$/;

                if (val === '') {
                    errorDiv.innerText = '❌ 請輸入身分證字號（欄位不可留空）';
                    inputElement.style.borderColor = '#e74c3c';
                    return false;
                } else if (!regex.test(val)) {
                    errorDiv.innerText = '❌ 身分證字號格式錯誤（須為1碼大寫英文 + 1或2 + 8碼數字）';
                    inputElement.style.borderColor = '#e74c3c';
                    return false;
                } else {
                    errorDiv.innerText = '';
                    inputElement.style.borderColor = '#3498db';
                    return true;
                }
            }

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

        function goToCheckout(concertId, zoneId, zoneText, ticketQty, merchSelections, attendeeData) {
            const merchJson = encodeURIComponent(JSON.stringify(merchSelections));
            const attendeeJson = encodeURIComponent(JSON.stringify(attendeeData)); 
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
