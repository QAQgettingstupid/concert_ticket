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
    $stmtOrder = $pdo->prepare("SELECT order_no, total_amount, status, created_at 
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
        .attendee-info-text {
            font-size: 0.88rem;
            color: #4a5568;
            background-color: #edf2f7;
            padding: 6px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
            border-left: 3px solid #3498db;
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
        .btn-delete {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.2s;
            display: inline-block;  /* 加這行 */
            white-space: nowrap;    /* 加這行，防止文字換行 */
            flex-shrink: 0;         /* 加這行，防止被壓縮 */
        }
        .btn-delete:hover { background-color: #c0392b; }
            
    </style>
</head>
            
            
            
<body>
          
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e2e8f0;">
        <h2>📦 我的訂單紀錄</h2>
        <a href="home.php" class="btn-back">回首頁</a>
    </div>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div style="background:#d1fae5; color:#065f46; padding:12px 20px; 
                            border-radius:8px; margin-bottom:20px; font-weight:bold;">
                    ✅ 訂單已成功刪除！
                </div>
            <?php endif; ?>
    <?php if (empty($orders)): ?>
        <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-light-subtle">
            <p style="color: #95a5a6; font-size: 1.1rem; margin: 0;">✨ 目前還沒有任何訂單紀錄喔！</p>
        </div>
    <?php else: ?>
        <div class="accordion" id="ordersAccordion">
            <?php foreach ($orders as $order): 
                $order_no = $order['order_no'];
                $is_unpaid = ($order['status'] === '未付款');
                
                // 撈出此訂單對應的全部明細
                $stmtItems = $pdo->prepare("SELECT item_type, quantity, unit_price, zone_id, merchandise_id, attendee_name, attendee_identity_no 
                                            FROM order_items 
                                            WHERE order_no = :order_no");
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
                        <div class="text-end d-flex flex-column align-items-end gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="status-badge <?= $is_unpaid ? 'status-unpaid' : 'status-paid' ?>">
                                    <?= $is_unpaid ? '⏳ 未付款' : '✅ 已付款' ?>
                                </span>
                                <span class="btn-delete"
                                        onclick="event.stopPropagation(); confirmDelete('<?= $order_no ?>')">
                                    🗑️ 刪除
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
                                            <td>
                                                <span class="badge" style="background-color: <?= $is_ticket ? '#3498db' : '#27ae60' ?>; font-size: 0.8rem; padding: 5px 8px;">
                                                    <?= $is_ticket ? '🎫 門票' : '🛍️ 周邊' ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <?php if ($is_ticket): ?>
                                                    <strong>票區 ID：</strong><?= htmlspecialchars($item['zone_id']) ?><br>
                                                    <div class="attendee-info-text">
                                                        👤 入場：<?= htmlspecialchars($item['attendee_name']) ?>（<?= htmlspecialchars($item['attendee_identity_no']) ?>）
                                                    </div>
                                                <?php else: ?>
                                                    <strong>周邊 ID：</strong><?= htmlspecialchars($item['merchandise_id'] ?? $item['merch_id']) ?>
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
<script>
function confirmDelete(orderNo) {
    if (confirm('確定要刪除訂單 #' + orderNo + ' 嗎？\n此動作無法復原！')) {
        window.location.href = 'delete_order.php?order_no=' + orderNo;
    }
}
</script>
</body>
</html>
