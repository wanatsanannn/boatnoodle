<?php
$pageTitle = 'รายงานยอดขาย';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

$dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['to'] ?? date('Y-m-d');

// ยอดรวม
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as total,
           COUNT(*) as count
    FROM payments
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$dateFrom, $dateTo]);
$summary = $stmt->fetch();

// ยอดรายวัน
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as sale_date,
           SUM(total_amount) as daily_total,
           COUNT(*) as daily_count
    FROM payments
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY sale_date
");
$stmt->execute([$dateFrom, $dateTo]);
$dailySales = $stmt->fetchAll();

// ยอดตามวิธีชำระ
$stmt = $pdo->prepare("
    SELECT method,
           SUM(total_amount) as total,
           COUNT(*) as count
    FROM payments
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY method
");
$stmt->execute([$dateFrom, $dateTo]);
$byMethod = $stmt->fetchAll();

$extraCSS = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <h4 class="mb-0"><i class="bi bi-graph-up"></i> รายงานยอดขาย</h4>
    <button type="button" class="btn btn-outline-dark bg-white" onclick="window.print()">
        <i class="bi bi-printer"></i> พิมพ์รายงาน
    </button>
</div>

<!-- ส่วนหัวสำหรับปริ้น -->
<div class="d-none d-print-block text-center mb-4">
    <h4>รายงานยอดขาย</h4>
    <p class="mb-0 text-muted">
        ประจำวันที่ <?= date('d/m/Y', strtotime($dateFrom)) ?> ถึง <?= date('d/m/Y', strtotime($dateTo)) ?>
    </p>
</div>

<!-- เลือกช่วงวันที่ -->
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
                <button class="btn btn-danger"><i class="bi bi-search"></i> ดูรายงาน</button>
            </div>
        </form>
    </div>
</div>

<!-- สรุป -->
<div class="row g-3 mb-4 print-row">
    <div class="col-md-4 col-4">
        <div class="stat-card position-relative" style="background:linear-gradient(135deg, #1e8449 0%, #27ae60 50%, #2ecc71 100%); color: #fff;">
            <i class="bi bi-cash-stack"></i>
            <div class="stat-number"><?= formatPrice($summary['total']) ?></div>
            <div class="stat-label">ยอดขายรวม</div>
        </div>
    </div>
    <div class="col-md-4 col-4">
        <div class="stat-card position-relative" style="background:linear-gradient(135deg, #1a5276 0%, #2980b9 50%, #3498db 100%); color: #fff;">
            <i class="bi bi-receipt"></i>
            <div class="stat-number"><?= $summary['count'] ?></div>
            <div class="stat-label">จำนวนบิล</div>
        </div>
    </div>
    <div class="col-md-4 col-4">
        <div class="stat-card position-relative" style="background:linear-gradient(135deg, #d35400 0%, #e67e22 50%, #f39c12 100%); color: #fff;">
            <i class="bi bi-calculator"></i>
            <div class="stat-number"><?= $summary['count'] > 0 ? formatPrice($summary['total'] / $summary['count']) : '฿0' ?></div>
            <div class="stat-label">เฉลี่ยต่อบิล</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- กราฟยอดขายรายวัน -->
    <div class="col-lg-8 col-12">
        <div class="card h-100 mb-0">
            <div class="card-header bg-white">ยอดขายรายวัน</div>
            <div class="card-body">
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ยอดตามวิธีชำระ -->
    <div class="col-lg-4 col-12">
        <div class="card h-100">
            <div class="card-header bg-white">วิธีชำระเงิน</div>
            <div class="card-body">
                <?php foreach ($byMethod as $m): ?>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span><?= $m['method'] === 'cash' ? '💵 เงินสด' : '📱 PromptPay' ?></span>
                        <strong><?= formatPrice($m['total']) ?> (<?= $m['count'] ?> บิล)</strong>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($byMethod)): ?>
                    <p class="text-muted text-center">ไม่มีข้อมูล</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($dailySales, 'sale_date')) ?>,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: <?= json_encode(array_map('floatval', array_column($dailySales, 'daily_total'))) ?>,
            backgroundColor: 'rgba(231, 76, 60, 0.7)',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
