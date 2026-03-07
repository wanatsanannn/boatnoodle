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

<!-- ส่วนหัวสำหรับปริ้น -->
<div class="d-none d-print-block text-center mb-4">
    <h4>รายงานเมนูขายดี</h4>
    <p class="mb-0 text-muted">
        ประจำวันที่ <?= date('d/m/Y', strtotime($dateFrom)) ?> ถึง <?= date('d/m/Y', strtotime($dateTo)) ?>
    </p>
</div>

<div class="card mb-4 d-print-none">
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

<div class="row">
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
