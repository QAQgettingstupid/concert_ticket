<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搶票系統 - 演唱會熱賣中 (呈現層)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background-color: #f8f9fa; color: #333; }
        h1 { text-align: center; margin-bottom: 40px; color: #2c3e50; }
        
        /* 設置卡片排版為網格(Grid)佈局，會自動隨螢幕寬度排版 */
        .concert-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 25px; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        
        /* 演唱會卡片樣式 */
        .concert-card { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            padding: 20px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
            transition: transform 0.2s;
        }
        .concert-card:hover { transform: translateY(-5px); }
        .concert-title { margin: 0 0 10px 0; font-size: 1.4rem; color: #2c3e50; }
        .concert-info { font-size: 0.95rem; color: #7f8c8d; margin-bottom: 20px; line-height: 1.5; }
        
        /* 搶票按鈕樣式 */
        .btn-buy { 
            background-color: #3498db; 
            color: white; 
            border: none; 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 1rem; 
            cursor: pointer; 
            font-weight: bold; 
            text-align: center;
            text-decoration: none;
        }
        .btn-buy:hover { background-color: #2980b9; }
        .status-soldout { background-color: #95a5a6; cursor: not-allowed; }
        .status-soldout:hover { background-color: #95a5a6; }

        /* 右上角登入按鈕樣式 */
        .login-btn-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .btn-login {
            background-color: #27ae60; /* 綠色，區隔於購票按鈕 */
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background-color: #219150;
        }
    </style>
</head>
<body>

    <div class="login-btn-container" id="user-status">
        <a href="login.php" class="btn-login">會員登入</a>
    </div>

    <h1>🎵 全球巨星演唱會搶票主頁</h1>
    
    <div class="concert-grid" id="concert-list"></div>

    <script>
        // 網頁一載入完成，立刻執行裡面的程式碼
        document.addEventListener('DOMContentLoaded', function() {

            // 1. 先查驗身份
            fetch('get_user_info.php')
            .then(response => response.json())
            .then(user => {
                const statusContainer = document.getElementById('user-status');
                if (user.is_logged_in) {
                    // 登入狀態：顯示名字 + 登出連結
                    statusContainer.innerHTML = `
                        <span style="margin-right: 15px;">您好，${user.name}</span>
                        <a href="logout.php" style="color: #e74c3c; font-weight: bold; text-decoration: none;">登出</a>
                    `;
                } else {
                    // 未登入狀態：顯示原本的登入按鈕
                    statusContainer.innerHTML = `<a href="login.php" class="btn-login">會員登入</a>`;
                }
            });
            
            // 1. 默默發送外送員去跟後端要演唱會資料
            fetch('get_concerts.php')
                .then(response => response.json()) // 解析 JSON
                .then(result => {
                    const listContainer = document.getElementById('concert-list');
                    listContainer.innerHTML = ''; // 清空畫面

                    if (result.status === 'success') {
                        // 2. 用迴圈撈出每一場演唱會
                        result.data.forEach(concert => {
                            const card = document.createElement('div');
                            card.className = 'concert-card';

                            // 判斷是否還有票，決定按鈕的外觀與功能
                            let buttonHTML = '';
                            if (concert.status === '預售中') {
                                // 還有票：點擊會跳轉到第二關（選區頁），並用網址參數帶上這場演唱會的 id
                                buttonHTML = `<a href="select_zone.php?concert_id=${concert.concert_id}" class="btn-buy">立即搶票</a>`;
                            } else {
                                // 沒票了：按鈕反灰，停用連結
                                buttonHTML = `<button class="btn-buy status-soldout" disabled>已售罄</button>`;
                            }

                            // 3. 填入卡片的 HTML 內容
                            card.innerHTML = `
                                <div>
                                    <h3 class="concert-title">${concert.title}</h3>
                                    <div class="concert-info">
                                        📍 地點：${concert.location}<br>
                                        📅 日期：${concert.dates}<br>
                                        ⏰ 訂票開始時間：${concert.time}
                                    </div>
                                </div>
                                ${buttonHTML}
                            `;

                            // 4. 把卡片塞進網格容器裡
                            listContainer.appendChild(card);
                        });
                    } else {
                        listContainer.innerHTML = `<p style="text-align:center; color:red;">無法載入活動清單：${result.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('發生錯誤:', error);
                    document.getElementById('concert-list').innerHTML = '<p style="text-align:center; color:red;">連線失敗，請檢查 get_concerts.php 是否正常</p>';
                });
        });
    </script>
</body>
</html>