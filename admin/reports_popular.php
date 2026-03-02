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

$extraJS = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="mb-4"><i class="bi bi-trophy"></i> เมนูขายดี</h4>

<div class="card mb-4">
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

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <canvas id="popularChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
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
</div>

<script>
const ctx = document.getElementById('popularChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($popular, 'name')) ?>,
        datasets: [{
            label: 'จำนวนที่ขายได้',
            data: <?= json_encode(array_map('intval', array_column($popular, 'total_qty'))) ?>,
            backgroundColor: [
                '#e74c3c', '#f39c12', '#27ae60', '#3498db', '#9b59b6',
                '#1abc9c', '#e67e22', '#2ecc71', '#e74c3c', '#34495e',
                '#16a085', '#d35400', '#c0392b', '#2980b9', '#8e44ad',
                '#27ae60', '#f1c40f', '#e74c3c', '#3498db', '#95a5a6'
            ],
            borderRadius: 4
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
