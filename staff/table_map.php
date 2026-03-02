<?php
$pageTitle = 'แผนผังโต๊ะ (Cashier Map)';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'cashier');

// Get all tables
$tables = $pdo->query("SELECT * FROM tables ORDER BY CAST(table_number AS UNSIGNED)")->fetchAll();

// Group tables into a dictionary for easy grid lookup
$tableMap = [];
foreach ($tables as $t) {
    $tableMap[$t['table_number']] = $t;
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.map-container {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 2rem;
    overflow-x: auto;
}

/* Left Grid (5x5) */
.grid-left {
    display: grid;
    grid-template-columns: repeat(5, 120px);
    grid-template-rows: repeat(5, 75px);
    gap: 30px;
}

/* Right Column (Vertical Tables) */
.column-right {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

/* Base Table Styling */
.t-node {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #95a5a6; /* Default gray if empty/unknown */
    color: white;
    font-weight: bold;
    font-size: 1.25rem;
    border-radius: 6px;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s, background-color 0.2s;
    text-decoration: none;
    position: relative;
    border: 2px solid transparent;
}

.t-node:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 10px rgba(0,0,0,0.15);
}

/* Status colors */
.t-node.status-available {
    background-color: #2ecc71; /* Green */
}
.t-node.status-occupied {
    background-color: #e74c3c; /* Red */
    border-color: #c0392b;
}

/* Sizes */
.t-normal {
    width: 120px;
    height: 75px;
}
.t-vertical {
    width: 80px;
    height: 145px;
}

.t-node span {
    z-index: 2;
}

.t-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.7);
    color: white;
    border-radius: 4px;
    opacity: 0;
    transition: opacity 0.2s;
    z-index: 5;
    gap: 8px;
}

.t-node:hover .t-overlay {
    opacity: 1;
}

.t-btn-act {
    background: white;
    border: none;
    color: #333;
    padding: 4px 10px;
    font-size: 0.85rem;
    border-radius: 20px;
    font-weight: 600;
}
.t-btn-act:hover {
    background: #f1c40f;
    color: #000;
}
.t-btn-act.danger-btn:hover {
    background: #e74c3c;
    color: white;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .grid-left {
        grid-template-columns: repeat(5, 80px);
        grid-template-rows: repeat(5, 55px);
        gap: 15px;
    }
    .t-normal {
        width: 80px;
        height: 55px;
    }
    .t-vertical {
        width: 60px;
        height: 100px;
    }
    .column-right {
        gap: 15px;
    }
    .t-node {
        font-size: 1rem;
    }
    .t-btn-act {
        font-size: 0.75rem;
        padding: 2px 6px;
    }
}

@media (max-width: 768px) {
    .map-container {
        flex-direction: column;
        align-items: center;
    }
    .grid-left {
        grid-template-columns: repeat(5, 60px);
        grid-template-rows: repeat(5, 45px);
        gap: 10px;
    }
    .t-normal {
        width: 60px;
        height: 45px;
    }
    .t-vertical {
        width: 80px;
        height: 50px;
    }
    /* Switch vertical tables to a horizontal layout beneath the main grid */
    .column-right {
        flex-direction: row;
        gap: 10px;
    }
    .t-node {
        font-size: 0.9rem;
    }
    .t-btn-act {
        font-size: 0.7rem;
        padding: 2px 4px;
        width: 90% !important;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-map"></i> แผนผังโต๊ะ (Cashier Map)</h4>
    <div>
        <span class="badge bg-success ms-2 rounded-pill px-3 py-2" style="font-size: 1rem;"><i class="bi bi-circle-fill"></i> ว่าง</span>
        <span class="badge bg-danger ms-2 rounded-pill px-3 py-2" style="font-size: 1rem;"><i class="bi bi-circle-fill"></i> ไม่ว่าง</span>
    </div>
</div>

<div class="map-container justify-content-center">
    <!-- Left 5x5 Grid -->
    <div class="grid-left">
        <?php 
        // Render rows based on the image: 
        // Row 1: 1, 6, 10, 14, 18
        // Row 2: 2, 7, 11, 15, 19
        // Row 3: 3, 8, 12, 16, 20
        // Row 4: 4, 9, 13, 17, 21
        // Row 5: 5, 22, 23, 24, 25
        $layoutLeft = [
            ['1', '6', '10', '14', '18'],
            ['2', '7', '11', '15', '19'],
            ['3', '8', '12', '16', '20'],
            ['4', '9', '13', '17', '21'],
            ['5', '22', '23', '24', '25']
        ];

        foreach ($layoutLeft as $row) {
            foreach ($row as $num) {
                renderMapTable($num, $tableMap, 't-normal');
            }
        }
        ?>
    </div>

    <!-- Right Column (Vertical Tables 28, 27, 26) -->
    <div class="column-right">
        <?php
        $layoutRight = ['28', '27', '26'];
        foreach ($layoutRight as $num) {
            renderMapTable($num, $tableMap, 't-vertical');
        }
        ?>
    </div>
</div>

<?php
function renderMapTable($tableNumber, $tableMap, $cssClass) {
    if (!isset($tableMap[$tableNumber])) {
        // Fallback for tables that don't exist in DB yet
        echo '<div class="t-node '.$cssClass.'" style="opacity:0.3;"><span>'.htmlspecialchars($tableNumber).'</span></div>';
        return;
    }
    
    $t = $tableMap[$tableNumber];
    $statusClass = $t['status'] === 'occupied' ? 'status-occupied' : 'status-available';
    $tokenStr = !empty($t['session_token']) ? '&token=' . $t['session_token'] : '';
    $orderUrl = BASE_URL . '/order/?table=' . urlencode($t['table_number']) . $tokenStr;
    $qrUrl = generateQRCodeURL($orderUrl);
    ?>
    
    <div class="t-node <?= $cssClass ?> <?= $statusClass ?>" id="table-node-<?= htmlspecialchars($t['table_number']) ?>">
        <span><?= htmlspecialchars($t['table_number']) ?></span>
        
        <!-- Hover Overlay -->
        <div class="t-overlay">
            <?php if ($t['status'] === 'occupied'): ?>
                <a href="payment.php?table=<?= $t['id'] ?>" class="t-btn-act text-decoration-none text-center d-block w-75">เช็คบิล</a>
            <?php else: ?>
                <button onclick="printQR('<?= e($t['table_number']) ?>', '<?= $qrUrl ?>')" class="t-btn-act w-75">ดู QR โค้ด</button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
}
?>

<script>
function printQR(tableNum, qrUrl) {
    const w = window.open('', '_blank', 'width=400,height=500');
    w.document.write(`
        <html><head><title>QR โต๊ะ ${tableNum}</title>
        <style>
            body { text-align: center; font-family: 'Sarabun', sans-serif; padding: 2rem; }
            h1 { font-size: 2rem; margin-bottom: .5rem; }
            h2 { font-size: 1.5rem; color: #c0392b; }
            img { width: 250px; margin: 1rem 0; }
            p { color: #666; }
        </style></head><body>
        <h1>ก๋วยเตี๋ยวเรือชาม</h1>
        <h2>โต๊ะ ${tableNum}</h2>
        <img id="qrImg" src="${qrUrl}" alt="QR Code"><br>
        <p>สแกนเพื่อสั่งอาหาร</p>
        <script>
            document.getElementById('qrImg').onload = function() {
                window.print();
            };
            document.getElementById('qrImg').onerror = function() {
                alert('ไม่สามารถโหลด QR Code ได้ กรุณาลองใหม่');
            };
        <\/script>
        </body></html>
    `);
    w.document.close();
}

// ระบบอัปเดตสถานะอัตโนมัติ (ทุก 5 วินาที)
function refreshTables() {
    fetch('../api/tables_status.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                data.tables.forEach(t => {
                    const el = document.getElementById('table-node-' + t.table_number);
                    if (el) {
                        const overlay = el.querySelector('.t-overlay');
                        if (t.status === 'occupied') {
                            el.classList.remove('status-available');
                            el.classList.add('status-occupied');
                            overlay.innerHTML = `<a href="payment.php?table=${t.id}" class="t-btn-act text-decoration-none text-center d-block w-75">เช็คบิล</a>`;
                        } else {
                            el.classList.remove('status-occupied');
                            el.classList.add('status-available');
                            const tokenStr = t.session_token ? '&token=' + t.session_token : '';
                            const orderUrl = '<?= BASE_URL ?>/order/?table=' + encodeURIComponent(t.table_number) + tokenStr;
                            const qrImageUrl = 'https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl=' + encodeURIComponent(orderUrl);
                            overlay.innerHTML = `<button onclick="printQR('${t.table_number}', '${qrImageUrl}')" class="t-btn-act w-75">ดู QR โค้ด</button>`;
                        }
                    }
                });
            }
        });
}

setInterval(refreshTables, 5000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
