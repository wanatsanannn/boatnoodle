<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$tableNumber = $_GET['table'] ?? '';
$token = $_GET['token'] ?? '';

// ตรวจสอบโต๊ะและโทเค็น
$stmt = $pdo->prepare("SELECT * FROM tables WHERE table_number = ?");
$stmt->execute([$tableNumber]);
$table = $stmt->fetch();
if (!$table || empty($table['session_token']) || $token !== $table['session_token']) {
    die('<div style="text-align:center;padding:3rem;font-family:Sarabun,sans-serif;">
            <h2>&#10060; สิทธิ์การเข้าถึงหมดอายุ</h2>
            <p>กรุณากลับไปสแกน QR Code ที่โต๊ะใหม่อีกครั้ง</p>
         </div>');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า — <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/customer.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body class="customer-body">

    <!-- Header -->
    <div class="shop-header" style="padding:1.25rem 1.1rem; border-radius: 0 0 16px 16px; box-shadow: 0 4px 12px rgba(230,57,70,0.15);">
        <div style="display:flex;align-items:center;justify-content:center;position:relative;margin-bottom:0.8rem;">
            <a href="index.php?table=<?= e($tableNumber) ?>&token=<?= e($token) ?>" style="color:#fff;text-decoration:none;font-size:1.5rem;display:flex;align-items:center;position:absolute;left:0;">
                <i class="bi bi-chevron-left" style="-webkit-text-stroke: 1px;"></i>
            </a>
            <h1 style="font-size:1.5rem;font-weight:700;margin:0;color:#fff;line-height:1.5rem;">ตะกร้าสินค้า</h1>
        </div>
        <div>
            <div class="table-badge" style="display:inline-block; font-weight:600; padding:0.25rem 0.8rem; font-size:0.95rem;">
                📍 โต๊ะ <?= e($tableNumber) ?>
            </div>
        </div>
    </div>

    <div class="px-3 py-3" style="padding-bottom: 120px !important;">
        <div id="cartItems">
            <!-- Rendered by JS -->
        </div>

        <div id="emptyCart" style="display:none;text-align:center;padding:2rem 1rem;min-height:70vh;display:flex;flex-direction:column;justify-content:center;align-items:center;">
            <div style="width: 160px; height: 160px; background-color: #fff1f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 2rem;">
                <i class="bi bi-cart3" style="font-size: 5rem; color: #e63946; opacity: 0.9;"></i>
            </div>
            <h3 style="color: #333; font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem;">ตะกร้ายังว่างอยู่เลย...</h3>
            <p style="color: #666; font-size: 0.95rem; margin-bottom: 3rem; line-height: 1.5;">มาเติมความอร่อยด้วยเมนูเด็ดของร้านเรา<br>กันเถอะ</p>
            <a href="index.php?table=<?= e($tableNumber) ?>&token=<?= e($token) ?>" class="text-decoration-none" style="display:inline-block;width:100%;max-width:320px;padding:1rem;font-size:1.1rem;font-weight:600;border-radius:50px;background-color:#e63946;color:#ffffff;box-shadow:0 4px 12px rgba(230,57,70,0.3);text-align:center;">
                เลือกดูเมนูอาหาร
            </a>
        </div>

        <div id="cartSummaryBox" style="display:none; flex-direction:column;">
            <div class="wf-double-line"></div>
            <div class="wf-total-row" id="cartTotal"></div>
        </div>

        <div class="wf-actions" id="cartActions" style="display:none;">
            <a href="index.php?table=<?= e($tableNumber) ?>&token=<?= e($token) ?>" class="wf-btn">
                + สั่งเพิ่ม
            </a>
            <button class="wf-btn" id="confirmBtn" onclick="submitOrder()">
                ยืนยันสั่งอาหาร
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/cart.js"></script>
    <script>
        const spiceLabels = ['ไม่เผ็ด', '🌶️ น้อย', '🌶️🌶️ กลาง', '🌶️🌶️🌶️ มาก'];

        function renderCart() {
            const items = Cart.getItems();
            const container = document.getElementById('cartItems');
            const emptyEl = document.getElementById('emptyCart');
            const totalEl = document.getElementById('cartTotal');
            const actionsEl = document.getElementById('cartActions');
            const summaryBox = document.getElementById('cartSummaryBox');

            if (items.length === 0) {
                container.innerHTML = '';
                emptyEl.style.display = 'block';
                summaryBox.style.display = 'none';
                actionsEl.style.display = 'none';
                return;
            }

            emptyEl.style.display = 'none';
            summaryBox.style.display = 'flex';
            actionsEl.style.display = 'flex';

            container.innerHTML = items.map((item, idx) => {
                let optText = '';
                if (item.options && item.options.length > 0) {
                    optText = item.options.map(o => o.name).join(', ');
                }
                
                let details = [];
                if (item.size === 'special') details.push('พิเศษ');
                if (item.spice_level > 0) details.push(spiceLabels[item.spice_level]);
                if (optText) details.push(optText);
                if (item.note) details.push('<span style="color:var(--primary-color)">* ' + item.note + '</span>');
                
                const detailHtml = details.length > 0 ? `<div class="wf-item-detail">${details.join(' | ')}</div>` : '';

                return `
                <div class="wf-cart-item">
                    <div class="wf-item-img-container">
                        <img src="../assets/uploads/menu/${item.image || 'default.png'}" class="wf-item-img" onerror="this.src='../assets/images/logo.png'">
                    </div>
                    <div class="wf-item-details-left">
                        <div class="wf-item-name">${item.name}</div>
                        ${detailHtml}
                    </div>
                    <div class="wf-item-right-panel">
                        <div class="wf-item-price-row">
                            <div class="wf-item-price">฿${item.subtotal.toFixed(2)}</div>
                            <div class="wf-item-remove" onclick="removeItem(${idx})">
                                <i class="bi bi-x"></i>
                            </div>
                        </div>
                        <div class="wf-qty-control">
                            <button type="button" onclick="updateQty(${idx}, ${item.quantity - 1})">-</button>
                            <span>${item.quantity}</span>
                            <button type="button" onclick="updateQty(${idx}, ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                </div>
            `}).join('');

            totalEl.innerHTML = 'รวมทั้งหมด : ฿' + Cart.getTotal().toFixed(2);
        }

        function updateQty(idx, qty) {
            Cart.updateQuantity(idx, qty);
            renderCart();
        }

        function removeItem(idx) {
            Cart.removeItem(idx);
            renderCart();
        }

        function submitOrder() {
            const items = Cart.getItems();
            if (items.length === 0) return;

            const btn = document.getElementById('confirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...';

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create_order',
                    table: '<?= e($tableNumber) ?>',
                    token: '<?= e($token) ?>',
                    items: items
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Cart.clear();
                    window.location.href = 'status.php?order=' + data.order_number + '&table=<?= e($tableNumber) ?>&token=<?= e($token) ?>';
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาด');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> ยืนยันสั่งอาหาร';
                }
            })
            .catch(() => {
                alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-circle"></i> ยืนยันสั่งอาหาร';
            });
        }

        renderCart();
    </script>
</body>
</html>
