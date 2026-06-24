<?php
session_start();

// 1. 檢查會員是否登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connect.php';
$user_id = $_SESSION['user_id'];

try {
    // 2. 撈取該會員的所有主訂單（依時間倒序排列，最新的在最上面）
    $stmtOrder = $pdo->prepare("SELECT order_no, total_amount, payment_status, created_at 
                                FROM orders 
                                WHERE identity_id = :user_id 
                                ORDER BY created_at DESC");
    $stmtOrder->execute([':user_id' => $user_id]);
    $orders = $stmtOrder->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("系統發生錯誤，無法載入訂單：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>我的訂單紀錄 - 搶票系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 💡 完美契合原本 select_zone 與 checkout 的白藍色調風格 */
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f8f9fa; 
            color: #333;
            padding: 40px 20px;
        }
        
        .container { 
            max-width: 850px; 
            margin: 0 auto; 
        }

        h2 { 
            color: #2c3e50; 
            margin-top: 0; 
            font-weight: bold;
        }

        /* 頂部回到首頁按鈕 */
        .btn-back {
            background-color: #95a5a6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-back:hover {
            background-color: #7f8c8d;
            color: white;
        }

        /* 手風琴折疊卡片外觀調校 */
        .accordion-item {
            background: white; 
            border-radius: 12px !important; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            overflow: hidden;
        }

        /* 自訂摺疊標頭 */
        .order-trigger-btn {
            width: 100%;
            text-align: left;
            background: white;
            border: none;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        /* 💡 當滑鼠移過去時，背景變為極淺的科技藍，給使用者「可點擊」的反饋 */
        .order-trigger-btn:hover {
            background-color: #f0f7fc;
        }
        .order-trigger-btn:focus {
            outline: none;
        }

        .order-no-title {
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 4px 0 0 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* 💡 精緻的天藍色「點擊查看」提示標籤 */
        .click-hint {
            font-size: 0.78rem;
            background-color: #e3f2fd;
            color: #3498db;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: normal;
            border: 1px solid #bbdefb;
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        /* 付款狀態標籤樣式 */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
        }
        .status-unpaid {
            background-color: #fff5f5;
            color: #e74c3c;
            border: 1px solid #feb2b2;
        }
        .status-paid {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .price-highlight {
            color: #e74c3c;
            font-weight: bold;
            font-size: 1.25rem;
        }

        /* 明細展示內部區塊 */
        .order-details-box {
            background-color: #f8fafc;
            border-top: 1px solid #edf2f7;
            padding: 25px;
        }

        .section-sub-title {
            color: #34495e;
            font-size: 1.05rem;
            font-weight: bold;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        /* 表格優化 */
        .table-custom {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .table-custom th {
            background-color: #edf2f7;
            color: #4a5568;
            font-weight: 600;
            border-bottom: 2px solid #cbd5e1;
            padding: 12px;
        }
        .table-custom td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf2f7;
            color: #2d3748;
        }

        /* 實名制小卡片風格 */
        .attendee-list-box {
            font-size: 0.88rem;
            color: #4a5568;
            background-color: #f1f5f9; /* 改用優雅的淺灰藍 */
            padding: 10px 14px;
            border-radius: 6px;
            display: block;
            margin-top: 8px;
            border-left: 4px solid #3498db; /* 保留好看的藍色左邊條 */
        }

        .attendee-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .attendee-list-box ul li {
            margin-bottom: 3px;
            line-height: 1.4;
        }

        .attendee-list-box ul li:last-child {
            margin-bottom: 0;
        }

        /* 付款按鈕 */
        .btn-pay-now {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn-pay-now:hover {
            background-color: #c0392b;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e2e8f0;">
        <h2>📦 我的訂單紀錄</h2>
        <a href="home.php" class="btn-back">回首頁</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-light-subtle">
            <p style="color: #95a5a6; font-size: 1.1rem; margin: 0;">✨ 目前還沒有任何訂單紀錄喔！</p>
        </div>
    <?php else: ?>
        <div class="accordion" id="ordersAccordion">
            <?php foreach ($orders as $order): 
                $order_no = $order['order_no'];
                $is_unpaid = ($order['payment_status'] === '未付款');
                
                // 💡 修改重點：多撈取 ticket_zones 中的 concert_title (或者關聯 concerts 表)
                // 這裡假設 ticket_zones 表或與之關聯的結構內有包含活動名稱。如果它是關聯到 concerts 表，通常會再 LEFT JOIN concerts ON ticket_zones.concert_id = concerts.concert_id
                $stmtItems = $pdo->prepare("
                    SELECT 
                        order_items.item_type, 
                        order_items.quantity, 
                        order_items.unit_price, 
                        order_items.attendee_name, 
                        order_items.attendee_identity_no,
                        merchandises.prod_name,
                        ticket_zones.zone_name,
                        event_name
                    FROM order_items
                    LEFT JOIN merchandises ON order_items.merchandise_id = merchandises.merchandise_id
                    LEFT JOIN ticket_zones ON order_items.zone_id = ticket_zones.zone_id
                    LEFT JOIN event_dates ON ticket_zones.date_id = event_dates.date_id
                    LEFT JOIN events ON event_dates.event_id = events.event_id
                    WHERE order_no = :order_no
                ");
                $stmtItems->execute([':order_no' => $order_no]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
            ?>
                
                <div class="accordion-item">
                    <button class="order-trigger-btn" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse-<?= $order_no ?>" 
                            aria-expanded="false">
                        <div>
                            <div style="color: #7f8c8d; font-size: 0.88rem;">📅 訂單日期：<?= htmlspecialchars($order['created_at']) ?></div>
                            <h3 class="order-no-title">
                                訂單編號：#<?= htmlspecialchars($order_no) ?>
                                <span class="click-hint">🔍 點擊看明細 ▼</span>
                            </h3>
                        </div>
                        <div class="text-end">
                            <div class="mb-2">
                                <span class="status-badge <?= $is_unpaid ? 'status-unpaid' : 'status-paid' ?>">
                                    <?= $is_unpaid ? '⏳ 未付款' : '✅ 已付款' ?>
                                </span>
                            </div>
                            <div class="price-highlight">$<?= number_format($order['total_amount']) ?> TWD</div>
                        </div>
                    </button>

                    <div id="collapse-<?= $order_no ?>" class="collapse" data-bs-parent="#ordersAccordion">
                        <div class="order-details-box">
                            <div class="section-sub-title">
                                🛒 購買項目明細
                            </div>
                            
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
                                    <?php foreach ($items as $item): 
                                        $subtotal = $item['unit_price'] * $item['quantity'];
                                        $is_ticket = (strtolower($item['item_type']) === 'ticket');
                                    ?>
                                        <tr>
                                            <td style="vertical-align: top; padding-top: 17px;">
                                                <span class="badge" style="background-color: <?= $is_ticket ? '#3498db' : '#27ae60' ?>; font-size: 0.8rem; padding: 5px 8px; display: inline-block;">
                                                    <?= $is_ticket ? '🎫 門票' : '🛍️ 周邊' ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <?php if ($is_ticket): ?>
                                                    <div class="d-flex align-items-center mb-1" style="line-height: 1.2;">
                                                        <strong class="me-1">活動：</strong>
                                                        <span><?= htmlspecialchars($item['event_name'] ?? '未知活動') ?></span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong>票區：</strong><?= htmlspecialchars($item['zone_name'] ?? $item['zone_id']) ?>
                                                    </div>
                                                    
                                                    <div class="attendee-list-box">
                                                        <div class="attendee-title">👤 入場人資訊：</div>
                                                        <ul class="list-unstyled mb-0 ps-2">
                                                            <li>
                                                                <strong>入場人姓名：</strong><?= htmlspecialchars($item['attendee_name']) ?>
                                                            </li>
                                                            <li>
                                                                <strong>身分證字號：</strong>
                                                                <?php 
                                                                    $id_no = $item['attendee_identity_no'];
                                                                    // 遮罩邏輯：如果長度足夠（通常10碼），將第4到第6碼替換為 ***
                                                                    if (strlen($id_no) >= 10) {
                                                                        $masked_id = substr($id_no, 0, 3) . '***' . substr($id_no, 6);
                                                                    } else {
                                                                        // 防止異常長度資料，做基本前後留尾
                                                                        $masked_id = substr($id_no, 0, 2) . '***' . substr($id_no, -2);
                                                                    }
                                                                    echo htmlspecialchars($masked_id);
                                                                ?>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <strong class="me-1">周邊：</strong>
                                                        <span><?= htmlspecialchars($item['prod_name']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="text-end" style="color: #7f8c8d;">$<?= number_format($item['unit_price']) ?></td>
                                            <td class="text-center fw-bold"><?= intval($item['quantity']) ?></td>
                                            <td class="text-end fw-bold" style="color: #2c3e50;">$<?= number_format($subtotal) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>