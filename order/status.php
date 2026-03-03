<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$orderNumber = $_GET['order'] ?? '';
$tableNumber = $_GET['table'] ?? '';
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะออเดอร์ — <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/customer.css" rel="stylesheet">
</head>
<body class="customer-body">

    <div class="shop-header" style="padding:1.25rem 1.1rem; border-radius: 0 0 16px 16px; box-shadow: 0 4px 12px rgba(230,57,70,0.15);">
        <div style="display:flex;align-items:center;justify-content:center;position:relative;margin-bottom:0.8rem;">
            <h1 style="font-size:1.5rem;font-weight:700;margin:0;color:#fff;line-height:1.5rem;"><?= SITE_NAME ?></h1>
        </div>
        <div>
            <div class="table-badge" style="display:inline-block; font-weight:600; padding:0.25rem 0.8rem; font-size:0.95rem;">
                📍 โต๊ะ <?= e($tableNumber) ?>
            </div>
        </div>
    </div>

    <div class="px-3 py-3">
        <div class="text-center mb-3">
            <h5>📋 ออเดอร์: <strong id="orderNum">#<?= e($orderNumber) ?></strong></h5>
            <p class="text-muted small">อัปเดตอัตโนมัติทุก 10 วินาที</p>
        </div>

        <div id="statusItems">
            <div class="text-center py-4">
                <div class="spinner-border text-danger"></div>
                <p class="mt-2">กำลังโหลด...</p>
            </div>
        </div>

        <div class="mt-4">
            <a href="index.php?table=<?= e($tableNumber) ?>&token=<?= e($token) ?>" class="btn btn-danger w-100 py-2">
                <i class="bi bi-plus-lg"></i> สั่งเพิ่ม
            </a>
            <button class="btn btn-outline-secondary w-100 py-2 mt-2" id="cancelBtn" style="display:none;" onclick="cancelOrder()">
                <i class="bi bi-x-circle"></i> ยกเลิกออเดอร์
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const statusLabels = {
            pending:   { text: 'รอรับออเดอร์', icon: '⏳', cls: 'status-pending' },
            cooking:   { text: 'กำลังเตรียม', icon: '🔥', cls: 'status-cooking' },
            ready:     { text: 'พร้อมเสิร์ฟ', icon: '✅', cls: 'status-ready' },
            served:    { text: 'เสิร์ฟแล้ว', icon: '🍜', cls: 'status-served' },
            cancelled: { text: 'ยกเลิกแล้ว', icon: '❌', cls: 'status-pending' }
        };

        function fetchStatus() {
            fetch('api.php?action=order_status&order=<?= e($orderNumber) ?>')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        renderStatus(data.items);
                        // แสดงปุ่มยกเลิกเฉพาะออเดอร์ที่ยัง pending
                        const cancelBtn = document.getElementById('cancelBtn');
                        cancelBtn.style.display = data.order_status === 'pending' ? 'block' : 'none';
                    } else {
                        document.getElementById('statusItems').innerHTML =
                            '<p class="text-center text-danger">ไม่พบข้อมูลออเดอร์</p>';
                    }
                });
        }

        function renderStatus(items) {
            const container = document.getElementById('statusItems');
            if (items.length === 0) {
                container.innerHTML = '<p class="text-center text-muted">ไม่มีรายการ</p>';
                return;
            }

            container.innerHTML = items.map(item => {
                const s = statusLabels[item.status] || statusLabels.pending;
                return `
                    <div class="status-item ${s.cls}">
                        <div>
                            <strong>${item.menu_name}</strong> × ${item.quantity}
                            <div class="small text-muted">
                                ${item.size === 'special' ? 'พิเศษ' : 'ธรรมดา'}
                                ${item.spice_level > 0 ? ' | เผ็ด ' + item.spice_level : ''}
                                ${item.options && item.options.length > 0 ? ' | ' + item.options.map(o => o.option_name + (parseFloat(o.extra_price) > 0 ? ' +฿' + parseFloat(o.extra_price).toFixed(0) : '')).join(', ') : ''}
                            </div>
                        </div>
                        <div class="text-end">
                            <span>${s.icon} ${s.text}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function cancelOrder() {
            if (!confirm('คุณต้องการยกเลิกออเดอร์นี้หรือไม่?')) return;

            const btn = document.getElementById('cancelBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังยกเลิก...';

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'cancel_order',
                    order_number: '<?= e($orderNumber) ?>'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('ยกเลิกออเดอร์เรียบร้อยแล้ว');
                    window.location.href = 'index.php?table=<?= e($tableNumber) ?>&token=<?= e($token) ?>';
                } else {
                    alert(data.message || 'ไม่สามารถยกเลิกได้');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-x-circle"></i> ยกเลิกออเดอร์';
                }
            })
            .catch(() => {
                alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x-circle"></i> ยกเลิกออเดอร์';
            });
        }

        fetchStatus();
        setInterval(fetchStatus, 10000);
    </script>
</body>
</html>
