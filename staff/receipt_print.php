<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager', 'cashier');

$paymentId = $_GET['id'] ?? 0;

if (!$paymentId) {
    die("Invalid request");
}

// 1. Fetch Payment Data
$stmt = $pdo->prepare("
    SELECT p.*, t.table_number, u.fullname as cashier_name
    FROM payments p
    LEFT JOIN tables t ON p.table_id = t.id
    LEFT JOIN users u ON p.received_by = u.id
    WHERE p.id = ?
");
$stmt->execute([$paymentId]);
$payment = $stmt->fetch();

if (!$payment) {
    die("Payment not found");
}

// 2. Fetch Orders that belong to this payment.
// Since we don't track payment_id directly on orders, we look for completed orders 
// on the same table that were finalized close to the payment creation time.
// We'll broaden the window to -5/+5 minutes just in case
$stmt = $pdo->prepare("
    SELECT o.*, 
        GROUP_CONCAT(
            CONCAT(oi.quantity, '× ', m.name, IF(oi.size='special',' (พิเศษ)',''),
                COALESCE((SELECT CONCAT(' [', GROUP_CONCAT(oio.option_name SEPARATOR ', '), ']') FROM order_item_options oio WHERE oio.order_item_id = oi.id), ''),
                '|', FORMAT(oi.subtotal, 2))
            SEPARATOR '||'
        ) as items_data
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN menu_items m ON oi.menu_item_id = m.id
    WHERE o.table_id = ? 
      AND o.status = 'completed'
      AND o.updated_at >= ? - INTERVAL 15 MINUTE
      AND o.updated_at <= ? + INTERVAL 15 MINUTE
    GROUP BY o.id
");
$stmt->execute([$payment['table_id'], $payment['created_at'], $payment['created_at']]);
$orders = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จรับเงิน #<?= $payment['id'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            color: #000;
            background: #fff;
            width: 300px;
            margin: 0 auto; /* Center on normal screens for preview */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .shop-name { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 3px 0; vertical-align: top; }
        
        @media print {
            body { 
                width: 100%; 
                margin: 0; 
                padding: 0;
            }
            @page { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center mb-3">
        <div class="shop-name"><?= SITE_NAME ?></div>
        <div>ใบเสร็จรับเงิน / Receipt</div>
    </div>

    <table class="mb-2">
        <tr>
            <td>Order ID:</td>
            <td class="text-right"><?= str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?></td>
        </tr>
        <tr>
            <td>วันที่:</td>
            <td class="text-right"><?= date('d/m/Y H:i', strtotime($payment['created_at'])) ?></td>
        </tr>
        <tr>
            <td>โต๊ะ:</td>
            <td class="text-right"><?= e($payment['table_number'] ?? '-') ?></td>
        </tr>
        <tr>
            <td>พนักงาน:</td>
            <td class="text-right"><?= e($payment['cashier_name']) ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="mb-2">
        <tr class="fw-bold">
            <td width="60%">รายการ</td>
            <td width="40%" class="text-right">จำนวนเงิน</td>
        </tr>
        <?php foreach ($orders as $order): ?>
            <?php 
                $items = explode('||', $order['items_data']);
                foreach ($items as $itemStr): 
                    $parts = explode('|', $itemStr);
                    $itemName = $parts[0] ?? '';
                    $itemPrice = $parts[1] ?? '0.00';
            ?>
                <tr>
                    <td><?= e($itemName) ?></td>
                    <td class="text-right"><?= e($itemPrice) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <?php if(empty($orders)): ?>
            <tr><td colspan="2" class="text-center text-muted">-- ไม่พบข้อมูลรายการอาหาร --</td></tr>
        <?php endif; ?>
    </table>

    <div class="divider"></div>

    <table class="mb-3">
        <tr class="fw-bold" style="font-size: 16px;">
            <td>ยอดสุทธิ:</td>
            <td class="text-right"><?= formatPrice($payment['total_amount']) ?></td>
        </tr>
        <tr>
            <td>วิธีชำระเงิน:</td>
            <td class="text-right">
                <?= $payment['method'] === 'cash' ? 'เงินสด' : 'โอนเงิน (PromptPay)' ?>
            </td>
        </tr>
    </table>

    <div class="text-center">
        <div>ขอบคุณที่ใช้บริการครับ/ค่ะ</div>
    </div>

</body>
</html>
