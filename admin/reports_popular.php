<?php
$pageTitle = 'เมนูขายดี';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

$dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['to'] ?? date('Y-m-d');
$limit = (int)($_GET['limit'] ?? 10);

$stmt = $pdo->prepare("
    SELECT m.name, m.image, c.name as category_name,
           SUM(oi.quantity) as total_qty,
           SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN menu_items m ON oi.menu_item_id = m.id
    JOIN categories c ON m.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'cancelled'
      AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY oi.menu_item_id
    ORDER BY total_qty DESC
    LIMIT ?
");
$stmt->execute([$dateFrom, $dateTo, $limit]);
$popular = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <h4 class="mb-0"><i class="bi bi-trophy"></i> เมนูขายดี</h4>
    <button type="button" class="btn btn-outline-dark bg-white" onclick="window.print()">
        <i class="bi bi-printer"></i> พิมพ์รายงาน
    </button>
</div>

<!-- รูปแบบการพิมพ์ แบบ POS มาตรฐาน -->
<style>
@media print {
    .pop-print {
        font-family: 'Sarabun', sans-serif !important;
        color: #000 !important;
        padding: 20px 40px !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
    }
    .pop-print * { color: #000 !important; }

    .pop-print .pr-head {
        text-align: center;
        margin-bottom: 20px;
    }
    .pop-print .pr-head h3 { margin: 0; font-size: 18px; font-weight: 800; }
    .pop-print .pr-head p { margin: 3px 0 0; font-size: 13px; }

    .pop-print .pr-row {
        display: flex !important;
        align-items: center;
        padding: 7px 5px;
        font-size: 13px;
        border-bottom: 1px dotted #ccc;
    }
    .pop-print .pr-row.header {
        font-weight: 700;
        font-size: 12px;
        border-bottom: 2px solid #000;
        padding-bottom: 8px;
        margin-bottom: 2px;
    }
    .pop-print .pr-row.alt {
        background: #f5f5f5 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .pop-print .pr-row .c-no { width: 40px; text-align: center; }
    .pop-print .pr-row .c-name { flex: 1; }
    .pop-print .pr-row .c-cat { width: 100px; }
    .pop-print .pr-row .c-qty { width: 80px; text-align: right; }
    .pop-print .pr-row .c-rev { width: 100px; text-align: right; font-weight: 600; }

    .pop-print .pr-row.total {
        border-top: 2px solid #000;
        border-bottom: none;
        font-weight: 700;
        font-size: 14px;
        padding-top: 10px;
        margin-top: 3px;
    }

    .pop-print .pr-foot {
        margin-top: 25px;
        font-size: 11px;
        text-align: center;
    }
}
</style>

<div class="d-none d-print-block pop-print">

    <div class="pr-head">
        <h3>ร้านก๋วยเตี๋ยวเรือชาม</h3>
        <p style="font-size:15px !important; font-weight:600;">รายงานเมนูขายดี Top <?= $limit ?></p>
        <p>ประจำวันที่ <?= date('d/m/Y', strtotime($dateFrom)) ?> ถึง <?= date('d/m/Y', strtotime($dateTo)) ?></p>
    </div>

    <!-- หัวตาราง -->
    <div class="pr-row header">
        <span class="c-no">อันดับ</span>
        <span class="c-name">ชื่อเมนู</span>
        <span class="c-cat">หมวดหมู่</span>
        <span class="c-qty">จำนวน</span>
        <span class="c-rev">ยอดขาย</span>
    </div>

    <!-- ข้อมูล -->
    <?php
    $totalQty = 0;
    $totalRevenue = 0;
    foreach ($popular as $i => $p):
        $totalQty += $p['total_qty'];
        $totalRevenue += $p['total_revenue'];
    ?>
    <div class="pr-row <?= $i % 2 === 1 ? 'alt' : '' ?>">
        <span class="c-no"><?= $i + 1 ?></span>
        <span class="c-name"><?= e($p['name']) ?></span>
        <span class="c-cat"><?= e($p['category_name']) ?></span>
        <span class="c-qty"><?= number_format($p['total_qty']) ?> ชาม</span>
        <span class="c-rev"><?= formatPrice($p['total_revenue']) ?></span>
    </div>
    <?php endforeach; ?>

    <?php if (empty($popular)): ?>
    <div class="pr-row"><span class="c-name" style="text-align:center; width:100%;">— ไม่มีข้อมูล —</span></div>
    <?php endif; ?>

    <!-- แถวรวม -->
    <?php if (!empty($popular)): ?>
    <div class="pr-row total">
        <span class="c-no"></span>
        <span class="c-name">รวมทั้งหมด</span>
        <span class="c-cat"></span>
        <span class="c-qty"><?= number_format($totalQty) ?> ชาม</span>
        <span class="c-rev"><?= formatPrice($totalRevenue) ?></span>
    </div>
    <?php endif; ?>

    <div class="pr-foot">
        พิมพ์โดย <?= e(currentUser()['fullname'] ?? 'ผู้ดูแลระบบ') ?> | <?= date('d/m/Y H:i') ?>
    </div>

</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label">จากวันที่</label>
                <input type="date" name="from" class="form-control" value="<?= e($dateFrom) ?>">
            </div>
            <div class="col-auto">
                <label class="form-label">ถึงวันที่</label>
                <input type="date" name="to" class="form-control" value="<?= e($dateTo) ?>">
            </div>
            <div class="col-auto">
                <label class="form-label">จำนวน</label>
                <select name="limit" class="form-select" style="min-width:80px;padding-right:2.25rem;">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>Top 10</option>
                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>Top 20</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-danger"><i class="bi bi-search"></i> ดูรายงาน</button>
            </div>
        </form>
    </div>
</div>

<div class="row no-print">
    <div class="col-12">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>เมนู</th>
                            <th>จำนวน</th>
                            <th>รายได้</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popular as $i => $p): ?>
                        <tr>
                            <td>
                                <?php if ($i < 3): ?>
                                    <span class="badge bg-danger"><?= $i + 1 ?></span>
                                <?php else: ?>
                                    <?= $i + 1 ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($p['name']) ?></strong>
                                <br><small class="text-muted"><?= e($p['category_name']) ?></small>
                            </td>
                            <td><?= number_format($p['total_qty']) ?> ชาม</td>
                            <td><?= formatPrice($p['total_revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($popular)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
