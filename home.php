<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搶票系統 - 演唱會熱賣中</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; background-color: #f8f9fa; color: #333; }
        
        /* 💡 保持大標題與上方功能按鈕的舒適距離 */
        h1 { 
            text-align: center; 
            margin-top: 80px; 
            margin-bottom: 40px; 
            color: #2c3e50; 
            font-weight: bold; 
        }
        
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

        /* 右上角登入與會員狀態區塊樣式 */
        .login-btn-container {
            position: absolute;
            top: 25px;
            right: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
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

        /* 查看訂單按鈕樣式 */
        .btn-orders {
            background-color: transparent;
            color: #3498db;
            border: 2px solid #3498db;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            transition: all 0.2s ease-in-out;
        }
        .btn-orders:hover {
            background-color: #3498db;
            color: white;
        }
        
        .user-name-text {
            color: #4a5568;
            font-weight: 500;
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
        document.addEventListener('DOMContentLoaded', function() {

            // 1. 先查驗身份
            fetch('get_user_info.php')
            .then(response => response.json())
            .then(user => {
                const statusContainer = document.getElementById('user-status');
                if (user.is_logged_in) {
                    // 💡 已登入：動態塞入「查看訂單」按鈕、使用者暱稱、登出按鈕
                    statusContainer.innerHTML = `
                        <a href="my_orders.php" class="btn-orders">📋 我的訂單紀錄</a>
                        <span class="user-name-text">您好，${user.name}</span>
                        <a href="logout.php" style="color: #e74c3c; font-weight: bold; text-decoration: none; font-size: 0.9rem;">登出</a>
                    `;
                } else {
                    // 💡 未登入：單純只顯示會員登入按鈕，絕對不會有訂單入口
                    statusContainer.innerHTML = `<a href="login.php" class="btn-login">會員登入</a>`;
                }
            });
            
            // 2. 默默發送外送員去跟後端要演唱會資料
            fetch('get_concerts.php')
                .then(response => response.json())
                .then(result => {
                    const listContainer = document.getElementById('concert-list');
                    listContainer.innerHTML = ''; 

                    if (result.status === 'success') {
                        result.data.forEach(concert => {
                            const card = document.createElement('div');
                            card.className = 'concert-card';

                            let buttonHTML = '';
                            if (concert.status === '預售中') {
                                buttonHTML = `<a href="select_zone.php?concert_id=${concert.concert_id}&concert-title=${concert.title}" class="btn-buy">立即搶票</a>`;
                            } else {
                                buttonHTML = `<button class="btn-buy status-soldout" disabled>已售罄</button>`;
                            }

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