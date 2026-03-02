<?php
// หน้าเมนูอาหารสำหรับลูกค้า (ไม่ต้อง login)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$tableNumber = $_GET['table'] ?? '';
if (!$tableNumber) {
    die('<div style="text-align:center;padding:3rem;font-family:Sarabun,sans-serif;">
            <h2>&#10060; ไม่พบหมายเลขโต๊ะ</h2>
            <p>กรุณาสแกน QR Code ที่โต๊ะ</p>
         </div>');
}

// ตรวจสอบโต๊ะ
$stmt = $pdo->prepare("SELECT * FROM tables WHERE table_number = ?");
$stmt->execute([$tableNumber]);
$table = $stmt->fetch();
if (!$table) {
    die('<div style="text-align:center;padding:3rem;font-family:Sarabun,sans-serif;">
            <h2>&#10060; ไม่พบโต๊ะนี้</h2>
            <p>กรุณาแจ้งพนักงาน</p>
         </div>');
}

// ดึงหมวดหมู่
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order")->fetchAll();

// ดึงเมนูทั้งหมด
$menuItems = $pdo->query("
    SELECT m.*, c.name as category_name
    FROM menu_items m
    JOIN categories c ON m.category_id = c.id
    WHERE c.status = 'active'
    ORDER BY c.sort_order, m.name
")->fetchAll();

// ดึงตัวเลือกเสริมสำหรับแต่ละเมนู
$optionsQuery = $pdo->query("
    SELECT miog.menu_item_id, og.id as group_id, og.name as group_name,
           og.type as group_type, og.required as group_required, og.sort_order as group_sort,
           oc.id as choice_id, oc.name as choice_name, oc.extra_price,
           oc.is_default, oc.sort_order as choice_sort
    FROM menu_item_option_groups miog
    JOIN option_groups og ON miog.option_group_id = og.id AND og.status = 'active'
    JOIN option_choices oc ON oc.group_id = og.id AND oc.status = 'active'
    ORDER BY og.sort_order, oc.sort_order
")->fetchAll();

// จัดกลุ่ม options ตาม menu_item_id
$menuOptions = [];
foreach ($optionsQuery as $row) {
    $mid = $row['menu_item_id'];
    $gid = $row['group_id'];
    if (!isset($menuOptions[$mid][$gid])) {
        $menuOptions[$mid][$gid] = [
            'id' => $gid,
            'name' => $row['group_name'],
            'type' => $row['group_type'],
            'required' => (bool)$row['group_required'],
            'choices' => []
        ];
    }
    $menuOptions[$mid][$gid]['choices'][] = [
        'id' => (int)$row['choice_id'],
        'name' => $row['choice_name'],
        'extra_price' => (float)$row['extra_price'],
        'is_default' => (bool)$row['is_default']
    ];
}
// แปลงเป็น indexed array
foreach ($menuOptions as &$groups) {
    $groups = array_values($groups);
}
unset($groups);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>สั่งอาหาร — <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/customer.css" rel="stylesheet">
</head>
<body class="customer-body">

    <!-- Header -->
    <div class="shop-header">
        <div class="header-top-row">
            <div class="header-left">
                <img src="../assets/images/logo.png" alt="Logo">
                <h1><?= SITE_NAME ?></h1>
            </div>
            <a href="cart.php?table=<?= e($tableNumber) ?>" class="header-cart-icon" id="headerCart">
                <i class="bi bi-cart3"></i>
                <span class="header-cart-badge" id="headerCartBadge">0</span>
            </a>
        </div>
        <div class="header-badges">
            <div class="table-badge">📍 โต๊ะ <?= e($tableNumber) ?></div>
            <div class="table-badge" id="btnTrackOrder" onclick="openStatusModal()" style="display:none;cursor:pointer">📋 ดูสถานะออเดอร์</div>
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="category-tabs">
        <div class="cat-tab active" data-cat="all">ทั้งหมด</div>
        <?php foreach ($categories as $cat): ?>
            <div class="cat-tab" data-cat="<?= $cat['id'] ?>"><?= e($cat['name']) ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="search-box">
        <div style="position:relative;">
            <i class="bi bi-search" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#adb5bd;font-size:1rem;"></i>
            <input type="text" id="searchInput" placeholder="ค้นหาเมนู..." style="padding-left:2.75rem;">
        </div>
    </div>

    <!-- Menu Grid -->
    <div class="menu-grid" id="menuGrid">
        <?php foreach ($menuItems as $item): ?>
        <div class="menu-card <?= $item['status'] === 'sold_out' ? 'sold-out' : '' ?>"
             data-cat="<?= $item['category_id'] ?>"
             data-name="<?= e(strtolower($item['name'])) ?>"
             onclick="openItemModal(<?= htmlspecialchars(json_encode([
                 'id' => $item['id'],
                 'name' => $item['name'],
                 'price_normal' => (float)$item['price_normal'],
                 'price_special' => $item['price_special'] ? (float)$item['price_special'] : null,
                 'has_spice' => (bool)$item['has_spice_option'],
                 'image' => $item['image'],
                 'description' => $item['description'],
                 'options' => $menuOptions[$item['id']] ?? []
             ]), ENT_QUOTES) ?>)">
            <?php if ($item['status'] === 'sold_out'): ?>
                <div class="sold-out-badge">หมด</div>
            <?php endif; ?>
            <?php if ($item['image']): ?>
                <img src="../assets/uploads/menu/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>">
            <?php else: ?>
                <div class="no-image"><i class="bi bi-egg-fried"></i></div>
            <?php endif; ?>
            <div class="card-body">
                <div class="menu-name"><?= e($item['name']) ?></div>
                <div class="menu-bottom">
                    <div class="menu-price"><?= formatPrice($item['price_normal']) ?>
                        <?php if ($item['price_special']): ?>
                            <small class="text-muted">/ <?= formatPrice($item['price_special']) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php if ($item['status'] !== 'sold_out'): ?>
                        <div class="menu-add-btn"><i class="bi bi-plus"></i></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Checkout Bar -->
    <div class="checkout-bar" id="checkoutBar" onclick="window.location.href='cart.php?table=<?= e($tableNumber) ?>'">
        <div class="checkout-left">
            <i class="bi bi-cart-check-fill"></i>
            <span class="checkout-count" id="checkoutCount">0</span> รายการ
        </div>
        <div class="checkout-right">
            <span class="checkout-total" id="checkoutTotal">฿0.00</span>
            <span class="checkout-btn"> <i class="bi bi-arrow-right"></i></span>
        </div>
    </div>

    <!-- Item Option Modal -->
    <div class="modal fade option-modal" id="itemModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalItemName"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="modalItemDesc" class="text-muted small"></p>

                    <!-- ขนาด -->
                    <div class="mb-3" id="sizeSection">
                        <label class="form-label fw-bold">ขนาด</label>
                        <div class="size-options">
                            <div class="size-btn active" data-size="normal" id="sizeNormal">ธรรมดา — <span id="priceNormal"></span></div>
                            <div class="size-btn" data-size="special" id="sizeSpecial">พิเศษ — <span id="priceSpecial"></span></div>
                        </div>
                    </div>

                    <!-- ความเผ็ด -->
                    <div class="mb-3" id="spiceSection">
                        <label class="form-label fw-bold">ระดับความเผ็ด</label>
                        <div class="spice-options">
                            <div class="spice-btn active" data-spice="0">ไม่เผ็ด</div>
                            <div class="spice-btn" data-spice="1">🌶️ น้อย</div>
                            <div class="spice-btn" data-spice="2">🌶️🌶️ กลาง</div>
                            <div class="spice-btn" data-spice="3">🌶️🌶️🌶️ มาก</div>
                        </div>
                    </div>

                    <!-- ตัวเลือกเสริม (render แบบ dynamic) -->
                    <div id="optionsContainer"></div>

                    <!-- จำนวน -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">จำนวน</label>
                        <div class="qty-control">
                            <button onclick="changeQty(-1)">−</button>
                            <span id="qtyDisplay">1</span>
                            <button onclick="changeQty(1)">+</button>
                        </div>
                    </div>

                    <!-- หมายเหตุ -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">หมายเหตุ (ถ้ามี)</label>
                        <input type="text" id="itemNote" class="form-control" placeholder="เช่น ไม่ใส่ผัก">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger w-100 py-2 fw-bold" onclick="addToCart()">
                        <i class="bi bi-cart-plus"></i> เพิ่มลงตะกร้า — <span id="addPrice">฿0</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:var(--radius-lg);border:none;overflow:hidden">
                <div class="modal-header" style="background:linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);color:#fff;border:none;padding:1.25rem">
                    <h5 class="modal-title fw-bold">📋 สถานะออเดอร์</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="background:var(--bg-color);padding:1rem;max-height:60vh" id="statusModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger"></div>
                        <p class="mt-2">กำลังโหลด...</p>
                    </div>
                </div>
                <div class="modal-footer" style="background:#fff;border-top:1px solid var(--border-color);padding:.75rem 1rem">
                    <small class="text-muted" style="width:100%;text-align:center"><i class="bi bi-arrow-repeat"></i> อัปเดตอัตโนมัติทุก 10 วินาที</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/cart.js"></script>
    <script>
        // เก็บหมายเลขโต๊ะ
        Cart.setTable('<?= e($tableNumber) ?>');

        let currentItem = null;
        let selectedSize = 'normal';
        let selectedSpice = 0;
        let qty = 1;
        let selectedOptions = {}; // { groupId: [choiceId, ...] }

        // เปิด modal ตัวเลือก
        function openItemModal(item) {
            currentItem = item;
            selectedSize = 'normal';
            selectedSpice = 0;
            qty = 1;
            selectedOptions = {};

            document.getElementById('modalItemName').textContent = item.name;
            document.getElementById('modalItemDesc').textContent = item.description || '';
            document.getElementById('priceNormal').textContent = '฿' + item.price_normal.toFixed(2);
            document.getElementById('qtyDisplay').textContent = 1;
            document.getElementById('itemNote').value = '';

            // ขนาด
            const sizeSection = document.getElementById('sizeSection');
            if (item.price_special) {
                sizeSection.style.display = 'block';
                document.getElementById('priceSpecial').textContent = '฿' + item.price_special.toFixed(2);
            } else {
                sizeSection.style.display = 'none';
            }

            // ความเผ็ด
            document.getElementById('spiceSection').style.display = item.has_spice ? 'block' : 'none';

            // Reset active states
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('.size-btn[data-size="normal"]').classList.add('active');
            document.querySelectorAll('.spice-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('.spice-btn[data-spice="0"]').classList.add('active');

            // Render ตัวเลือกเสริม
            renderOptions(item.options || []);

            updateAddPrice();
            new bootstrap.Modal(document.getElementById('itemModal')).show();
        }

        // Render กลุ่มตัวเลือกเสริม
        function renderOptions(optionGroups) {
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';

            optionGroups.forEach(group => {
                const div = document.createElement('div');
                div.className = 'mb-3';
                div.innerHTML = `
                    <label class="form-label fw-bold">
                        ${group.name}
                        ${group.required ? '<span class="text-danger">*</span>' : '<small class="text-muted">(ไม่บังคับ)</small>'}
                    </label>
                    <div class="${group.type === 'single' ? 'size-options' : 'size-options'}" id="optGroup_${group.id}">
                        ${group.choices.map(c => {
                            const priceText = c.extra_price > 0 ? ` +฿${c.extra_price.toFixed(0)}` : '';
                            const isDefault = c.is_default && group.type === 'single';
                            return `<div class="size-btn opt-btn ${isDefault ? 'active' : ''}"
                                         data-group="${group.id}" data-choice="${c.id}"
                                         data-price="${c.extra_price}" data-type="${group.type}"
                                         onclick="selectOption(this)">
                                        ${c.name}${priceText}
                                    </div>`;
                        }).join('')}
                    </div>
                `;
                container.appendChild(div);

                // ตั้งค่า default
                if (group.type === 'single') {
                    const defaultChoice = group.choices.find(c => c.is_default);
                    if (defaultChoice) {
                        selectedOptions[group.id] = [defaultChoice.id];
                    }
                }
            });
        }

        // เลือกตัวเลือก
        function selectOption(el) {
            const groupId = el.dataset.group;
            const choiceId = parseInt(el.dataset.choice);
            const type = el.dataset.type;

            if (type === 'single') {
                // Radio behavior — เลือกได้ 1 อย่าง
                document.querySelectorAll(`#optGroup_${groupId} .opt-btn`).forEach(b => b.classList.remove('active'));
                el.classList.add('active');
                selectedOptions[groupId] = [choiceId];
            } else {
                // Checkbox behavior — toggle
                el.classList.toggle('active');
                if (!selectedOptions[groupId]) selectedOptions[groupId] = [];
                const idx = selectedOptions[groupId].indexOf(choiceId);
                if (idx >= 0) {
                    selectedOptions[groupId].splice(idx, 1);
                } else {
                    selectedOptions[groupId].push(choiceId);
                }
            }
            updateAddPrice();
        }

        // คำนวณราคา options เพิ่ม
        function getOptionsExtraPrice() {
            let extra = 0;
            document.querySelectorAll('.opt-btn.active').forEach(el => {
                extra += parseFloat(el.dataset.price) || 0;
            });
            return extra;
        }

        // Size buttons
        document.querySelectorAll('.size-btn:not(.opt-btn)').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.classList.contains('opt-btn')) return;
                document.querySelectorAll('.size-btn:not(.opt-btn)').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedSize = this.dataset.size;
                updateAddPrice();
            });
        });

        // Spice buttons
        document.querySelectorAll('.spice-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.spice-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedSpice = parseInt(this.dataset.spice);
            });
        });

        function changeQty(delta) {
            qty = Math.max(1, qty + delta);
            document.getElementById('qtyDisplay').textContent = qty;
            updateAddPrice();
        }

        function updateAddPrice() {
            const basePrice = selectedSize === 'special' && currentItem.price_special
                ? currentItem.price_special : currentItem.price_normal;
            const optExtra = getOptionsExtraPrice();
            const total = (basePrice + optExtra) * qty;
            document.getElementById('addPrice').textContent = '฿' + total.toFixed(2);
        }

        // สร้าง array ของ selected options สำหรับเก็บในตะกร้า
        function getSelectedOptionsArray() {
            const result = [];
            document.querySelectorAll('.opt-btn.active').forEach(el => {
                result.push({
                    choice_id: parseInt(el.dataset.choice),
                    group_id: parseInt(el.dataset.group),
                    name: el.textContent.trim().replace(/\s*\+฿\d+/, ''),
                    extra_price: parseFloat(el.dataset.price) || 0
                });
            });
            return result;
        }

        function addToCart() {
            // ตรวจสอบ required options
            if (currentItem.options) {
                for (const group of currentItem.options) {
                    if (group.required) {
                        const selected = selectedOptions[group.id] || [];
                        if (selected.length === 0) {
                            alert('กรุณาเลือก ' + group.name);
                            return;
                        }
                    }
                }
            }

            const basePrice = selectedSize === 'special' && currentItem.price_special
                ? currentItem.price_special : currentItem.price_normal;
            const opts = getSelectedOptionsArray();
            const optExtra = opts.reduce((sum, o) => sum + o.extra_price, 0);
            const unitPrice = basePrice + optExtra;

            Cart.addItem({
                menu_id: currentItem.id,
                name: currentItem.name,
                size: selectedSize,
                spice_level: currentItem.has_spice ? selectedSpice : 0,
                quantity: qty,
                unit_price: unitPrice,
                subtotal: unitPrice * qty,
                note: document.getElementById('itemNote').value.trim(),
                options: opts
            });

            bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
        }

        // Category filter
        document.querySelectorAll('.cat-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const catId = this.dataset.cat;
                document.querySelectorAll('.menu-card').forEach(card => {
                    card.style.display = (catId === 'all' || card.dataset.cat === catId) ? '' : 'none';
                });
            });
        });

        // Search
        document.getElementById('searchInput').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.menu-card').forEach(card => {
                card.style.display = card.dataset.name.includes(q) ? '' : 'none';
            });
            // Reset category tabs
            document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
            document.querySelector('.cat-tab[data-cat="all"]').classList.add('active');
        });

        // === Order Status Tracking ===
        const statusLabels = {
            pending: { text: 'รอรับออเดอร์', icon: '⏳', color: '#adb5bd' },
            cooking: { text: 'กำลังเตรียม', icon: '🔥', color: '#f4a261' },
            ready:   { text: 'พร้อมเสิร์ฟ', icon: '✅', color: '#2a9d8f' },
            served:  { text: 'เสิร์ฟแล้ว', icon: '🍜', color: '#457b9d' }
        };

        let statusInterval = null;

        function openStatusModal() {
            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
            fetchTableOrders();
            statusInterval = setInterval(fetchTableOrders, 10000);
            document.getElementById('statusModal').addEventListener('hidden.bs.modal', () => {
                clearInterval(statusInterval);
            }, { once: true });
        }

        function fetchTableOrders() {
            fetch('api.php?action=table_orders&table=<?= e($tableNumber) ?>')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        renderStatusModal(data.orders);
                        // แสดงปุ่มถ้ามีออเดอร์
                        document.getElementById('btnTrackOrder').style.display =
                            data.orders.length > 0 ? '' : 'none';
                    }
                })
                .catch(() => {});
        }

        function renderStatusModal(orders) {
            const body = document.getElementById('statusModalBody');
            if (orders.length === 0) {
                body.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-inbox" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.4"></i>ยังไม่มีออเดอร์</div>';
                return;
            }

            const steps = ['pending', 'cooking', 'ready', 'served'];
            const stepInfo = {
                pending: { label: 'รอรับ', icon: '⏳' },
                cooking: { label: 'เตรียม', icon: '🔥' },
                ready:   { label: 'พร้อม', icon: '✅' },
                served:  { label: 'เสิร์ฟ', icon: '🍜' }
            };

            body.innerHTML = orders.map(order => {
                const currentStep = steps.indexOf(order.status);

                const progressBar = steps.map((step, i) => {
                    const info = stepInfo[step];
                    const done = i <= currentStep;
                    const active = i === currentStep;
                    return `
                        <div style="flex:1;text-align:center;position:relative">
                            <div style="width:28px;height:28px;border-radius:50%;margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:.75rem;
                                background:${done ? (active ? '#e63946' : '#fca5a5') : '#e9ecef'};
                                color:${done ? '#fff' : '#adb5bd'};
                                box-shadow:${active ? '0 0 0 4px rgba(230,57,70,.2)' : 'none'};
                                transition:all .3s">${info.icon}</div>
                            <div style="font-size:.65rem;margin-top:3px;font-weight:${active ? '700' : '400'};color:${done ? '#e63946' : '#adb5bd'}">${info.label}</div>
                        </div>`;
                }).join('');

                const progressLine = `
                    <div style="position:absolute;top:14px;left:15%;right:15%;height:3px;background:#e9ecef;border-radius:2px;z-index:0">
                        <div style="height:100%;width:${Math.max(0, currentStep) / (steps.length - 1) * 100}%;background:linear-gradient(90deg,#fca5a5,#e63946);border-radius:2px;transition:width .5s"></div>
                    </div>`;

                return `
                    <div style="background:#fff;border-radius:16px;margin-bottom:.75rem;padding:1rem;box-shadow:0 2px 8px rgba(0,0,0,.05);border:1px solid rgba(0,0,0,.04)">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
                            <span style="font-weight:700;font-size:.95rem;color:#1d3557">#${order.order_number}</span>
                            <span style="font-size:.78rem;color:#6c757d">${order.time_ago}</span>
                        </div>
                        <div style="position:relative;display:flex;margin-bottom:.75rem">
                            ${progressLine}
                            ${progressBar}
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:.35rem">
                            ${order.items.map(item => `
                                <span style="background:#fef2f2;color:#b91c1c;padding:.2rem .55rem;border-radius:6px;font-size:.78rem;font-weight:500">
                                    ${item.menu_name} ×${item.quantity}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // เช็คว่ามีออเดอร์ไหม → แสดงปุ่ม
        fetchTableOrders();

        // === Auto-hide header on scroll ===
        (function() {
            const header = document.querySelector('.shop-header');
            let lastScrollY = window.scrollY;
            let ticking = false;

            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        const currentScrollY = window.scrollY;
                        if (currentScrollY > lastScrollY && currentScrollY > 80) {
                            header.classList.add('header-hidden');
                        } else {
                            header.classList.remove('header-hidden');
                        }
                        lastScrollY = currentScrollY;
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        })();
    </script>
</body>
</html>
