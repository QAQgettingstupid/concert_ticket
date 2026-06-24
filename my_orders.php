<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>我的訂單紀錄 - 搶票系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f8f9fa; 
            color: #333;
            padding: 40px 20px;
        }
        .container { max-width: 850px; margin: 0 auto; }
        h2 { color: #2c3e50; margin-top: 0; font-weight: bold; }
        .btn-back {
            background-color: #95a5a6; color: white; border: none; padding: 8px 16px;
            border-radius: 6px; font-weight: bold; text-decoration: none; font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-back:hover { background-color: #7f8c8d; color: white; }
        .accordion-item {
            background: white; border-radius: 12px !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0; margin-bottom: 20px; overflow: hidden;
        }
        .order-trigger-btn {
            width: 100%; text-align: left; background: white; border: none; padding: 20px;
            display: flex; justify-content: space-between; align-items: center; cursor: pointer;
            transition: background-color 0.2s;
        }
        .order-trigger-btn:hover { background-color: #f0f7fc; }
        .order-trigger-btn:focus { outline: none; }
        .order-no-title { color: #2c3e50; font-size: 1.2rem; font-weight: bold; margin: 4px 0 0 0; display: flex; align-items: center; gap: 10px; }
        .click-hint { font-size: 0.78rem; background-color: #e3f2fd; color: #3498db; padding: 3px 8px; border-radius: 4px; font-weight: normal; border: 1px solid #bbdefb; display: inline-flex; align-items: center; gap: 2px; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; display: inline-block; }
        .status-unpaid { background-color: #fff5f5; color: #e74c3c; border: 1px solid #feb2b2; }
        .status-paid { background-color: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .price-highlight { color: #e74c3c; font-weight: bold; font-size: 1.25rem; }
        .order-details-box { background-color: #f8fafc; border-top: 1px solid #edf2f7; padding: 25px; }
        .section-sub-title { color: #34495e; font-size: 1.05rem; font-weight: bold; margin-bottom: 12px; display: flex; align-items: center; }
        .table-custom { background: white; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .table-custom th { background-color: #edf2f7; color: #4a5568; font-weight: 600; border-bottom: 2px solid #cbd5e1; padding: 12px; }
        .table-custom td { padding: 14px 12px; border-bottom: 1px solid #edf2f7; color: #2d3748; }
        .attendee-list-box { font-size: 0.88rem; color: #4a5568; background-color: #f1f5f9; padding: 10px 14px; border-radius: 6px; display: block; margin-top: 8px; border-left: 4px solid #3498db; }
        .attendee-title { font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .attendee-list-box ul li { margin-bottom: 3px; line-height: 1.4; }
        .attendee-list-box ul li:last-child { margin-bottom: 0; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e2e8f0;">
        <h2>📦 我的訂單紀錄</h2>
        <a href="home.php" class="btn-back">回首頁</a>
    </div>

    <div id="orders-container">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">載入中...</span>
            </div>
            <p class="mt-2 text-muted">正在努力載入您的訂單紀錄...</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 網頁載入完成後，發送 fetch 請求給後端 API
    fetch('get_orders.php')
        .then(response => response.json())
        .then(res => {
            const container = document.getElementById('orders-container');
            
            // 處理錯誤狀態
            if (res.status === 'error') {
                container.innerHTML = `
                    <div class="alert alert-danger text-center" role="alert">
                        ❌ ${res.message}
                    </div>
                `;
                return;
            }

            const orders = res.data;

            // 如果沒有訂單紀錄
            if (!orders || orders.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-light-subtle">
                        <p style="color: #95a5a6; font-size: 1.1rem; margin: 0;">✨ 目前還沒有任何訂單紀錄喔！</p>
                    </div>
                `;
                return;
            }

            // 開始組合手風琴 HTML 內容
            let accordionHTML = '<div class="accordion" id="ordersAccordion">';
            
            orders.forEach(order => {
                const badgeClass = order.is_unpaid ? 'status-unpaid' : 'status-paid';
                const statusText = order.is_unpaid ? '⏳ 未付款' : '✅ 已付款';
                
                // 產生購買明細 Table Rows
                let itemsRowsHTML = '';
                order.items.forEach(item => {
                    const typeBadgeColor = item.is_ticket ? '#3498db' : '#27ae60';
                    const typeText = item.is_ticket ? '🎫 門票' : '🛍️ 周邊';
                    
                    let detailsHTML = '';
                    if (item.is_ticket) {
                        detailsHTML = `
                            <div class="d-flex align-items-center mb-1" style="line-height: 1.2;">
                                <strong><span>${escapeHtml(item.event_name || '未知活動')}</span></strong>
                            </div>
                            <div class="mb-2">
                                <strong>票區：</strong>${escapeHtml(item.zone_name || '未知票區')}
                            </div>
                            <div class="attendee-list-box">
                                <div class="attendee-title">👤 入場人資訊：</div>
                                <ul class="list-unstyled mb-0 ps-2">
                                    <li><strong>入場人姓名：</strong>${escapeHtml(item.attendee_name)}</li>
                                    <li><strong>身分證字號：</strong>${escapeHtml(item.masked_id)}</li>
                                </ul>
                            </div>
                        `;
                    } else {
                        detailsHTML = `
                            <div class="d-flex align-items-center">
                                <strong><span>${escapeHtml(item.prod_name || '未知商品')}</span></strong>
                            </div>
                        `;
                    }

                    itemsRowsHTML += `
                        <tr>
                            <td style="vertical-align: top; padding-top: 17px;">
                                <span class="badge" style="background-color: ${typeBadgeColor}; font-size: 0.8rem; padding: 5px 8px; display: inline-block;">
                                    ${typeText}
                                </span>
                            </td>
                            <td>${detailsHTML}</td>
                            <td class="text-end" style="color: #7f8c8d;">$${item.formatted_unit_price}</td>
                            <td class="text-center fw-bold">${parseInt(item.quantity)}</td>
                            <td class="text-end fw-bold" style="color: #2c3e50;">$${item.formatted_subtotal}</td>
                        </tr>
                    `;
                });

                // 組合單個訂單卡片
                accordionHTML += `
                    <div class="accordion-item">
                        <button class="order-trigger-btn" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-${order.order_no}" 
                                aria-expanded="false">
                            <div>
                                <div style="color: #7f8c8d; font-size: 0.88rem;">📅 訂單日期：${escapeHtml(order.created_at)}</div>
                                <h3 class="order-no-title">
                                    訂單編號：#${escapeHtml(order.order_no)}
                                    <span class="click-hint">🔍 點擊看明細 ▼</span>
                                </h3>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">
                                    <span class="status-badge ${badgeClass}">${statusText}</span>
                                </div>
                                <div class="price-highlight">$${escapeHtml(order.formatted_total)} TWD</div>
                            </div>
                        </button>

                        <div id="collapse-${order.order_no}" class="collapse" data-bs-parent="#ordersAccordion">
                            <div class="order-details-box">
                                <div class="section-sub-title">🛒 購買項目明細</div>
                                <table class="table table-custom align-middle small">
                                    <thead>
                                        <tr>
                                            <th style="width: 18%;">項目類型</th>
                                            <th style="width: 44%;">詳情</th>
                                            <th style="width: 14%;" class="text-end">單價</th>
                                            <th style="width: 10%;" class="text-center">數量</th>
                                            <th style="width: 14%;" class="text-end">小計</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsRowsHTML}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            });

            accordionHTML += '</div>';
            container.innerHTML = accordionHTML;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('orders-container').innerHTML = `
                <div class="alert alert-danger text-center" role="alert">
                    ❌ 網路請求失敗，無法載入訂單。
                </div>
            `;
        });
});

// XSS 防護用轉義函式
function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
</body>
</html>