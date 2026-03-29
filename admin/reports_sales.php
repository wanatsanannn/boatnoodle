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

<!-- รูปแบบการพิมพ์ (Print Layout) -->
<style>
@media print {
    .print-report * { 
        color: #000 !important; 
        font-family: 'Sarabun', sans-serif !important;
    }
    .print-report { 
        padding: 20px 40px !important; 
        font-size: 14px !important; 
        line-height: 1.6 !important; 
    }
    .print-header { 
        text-align: center; 
        padding-bottom: 15px; 
        margin-bottom: 20px; 
        border-bottom: 2px solid #000; 
    }
    .print-header h2 { 
        font-size: 22px; 
        font-weight: 800; 
        margin: 0 0 2px; 
        letter-spacing: 1px; 
    }
    .print-header p { 
        margin: 0; 
        font-size: 13px; 
    }
    .print-section { 
        margin-bottom: 22px; 
    }
    .print-section-title { 
        font-size: 14px; 
        font-weight: 700; 
        margin: 0 0 10px; 
        padding-bottom: 5px; 
        border-bottom: 1px solid #555; 
        letter-spacing: 0.5px;
    }
    .print-row { 
        display: flex !important; 
        justify-content: space-between; 
        padding: 4px 0; 
        font-size: 13px; 
    }
    .print-row.alt { 
        background: #f5f5f5 !important; 
        -webkit-print-color-adjust: exact !important; 
        print-color-adjust: exact !important;
        padding: 4px 8px;
    }
    .print-row .label { flex: 1; }
    .print-row .value { 
        text-align: right; 
        font-weight: 600; 
        min-width: 120px; 
    }
    .print-row .count { 
        text-align: center; 
        min-width: 80px; 
    }
    .print-total-row {
        display: flex !important;
        justify-content: space-between;
        padding: 8px 0;
        margin-top: 5px;
        border-top: 1px solid #555;
        border-bottom: 2px solid #000;
        font-size: 15px;
        font-weight: 700;
    }
    .print-footer { 
        margin-top: 50px; 
        display: flex !important; 
        justify-content: space-between; 
    }
    .print-footer-left { font-size: 12px; }
    .print-footer-left p { margin: 0; }
    .print-signature { 
        text-align: center; 
        font-size: 12px; 
    }
    .print-signature .sign-line { 
        display: inline-block; 
        width: 180px; 
        border-bottom: 1px dotted #000; 
        margin-bottom: 3px; 
        height: 30px; 
    }
    .print-signature p { margin: 0; }
    .print-doc-id {
        text-align: right;
        font-size: 11px;
        margin-bottom: 15px;
    }
}
</style>

<div class="d-none d-print-block print-report">

    <div class="print-doc-id">
        เลขที่เอกสาร: RPT-<?= date('Ymd-His') ?>
    </div>

    <div class="print-header">
        <h2>ร้านก๋วยเตี๋ยวเรือชาม</h2>
        <p style="font-size: 15px !important; font-weight: 600; margin-top: 3px !important;">รายงานสรุปยอดขาย</p>
        <p>ประจำวันที่ <?= date('d/m/Y', strtotime($dateFrom)) ?> ถึง <?= date('d/m/Y', strtotime($dateTo)) ?></p>
    </div>

    <!-- สรุปยอดขาย -->
    <div class="print-section">
        <div class="print-section-title">สรุปภาพรวม</div>
        <div class="print-row">
            <span class="label">ยอดขายรวมทั้งหมด</span>
            <span class="value" style="font-size: 16px !important;"><?= formatPrice($summary['total']) ?></span>
        </div>
        <div class="print-row">
            <span class="label">จำนวนบิลทั้งหมด</span>
            <span class="value"><?= $summary['count'] ?> บิล</span>
        </div>
        <div class="print-row">
            <span class="label">ยอดเฉลี่ยต่อบิล</span>
            <span class="value"><?= $summary['count'] > 0 ? formatPrice($summary['total'] / $summary['count']) : '฿0' ?></span>
        </div>
    </div>

    <!-- แยกตามวิธีชำระ -->
    <div class="print-section">
        <div class="print-section-title">แยกตามวิธีชำระเงิน</div>
        <div class="print-row" style="font-weight: 700; font-size: 12px !important; color: #555 !important; margin-bottom: 4px;">
            <span class="label">วิธีชำระ</span>
            <span class="count">จำนวนบิล</span>
            <span class="value">ยอดเงิน</span>
        </div>
        <?php foreach ($byMethod as $i => $m): ?>
        <div class="print-row <?= $i % 2 === 0 ? 'alt' : '' ?>">
            <span class="label"><?= $m['method'] === 'cash' ? 'เงินสด' : 'PromptPay' ?></span>
            <span class="count"><?= $m['count'] ?></span>
            <span class="value"><?= formatPrice($m['total']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($byMethod)): ?>
        <div class="print-row"><span class="label">— ไม่มีข้อมูล —</span></div>
        <?php endif; ?>
    </div>

    <!-- รายละเอียดรายวัน -->
    <div class="print-section">
        <div class="print-section-title">รายละเอียดยอดขายรายวัน</div>
        <div class="print-row" style="font-weight: 700; font-size: 12px !important; color: #555 !important; margin-bottom: 4px;">
            <span class="label">วันที่</span>
            <span class="count">จำนวนบิล</span>
            <span class="value">ยอดขาย</span>
        </div>
        <?php foreach ($dailySales as $i => $day): ?>
        <div class="print-row <?= $i % 2 === 0 ? 'alt' : '' ?>">
            <span class="label"><?= date('d/m/Y', strtotime($day['sale_date'])) ?></span>
            <span class="count"><?= $day['daily_count'] ?></span>
            <span class="value"><?= formatPrice($day['daily_total']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($dailySales)): ?>
        <div class="print-row"><span class="label">— ไม่มีข้อมูล —</span></div>
        <?php endif; ?>
        <div class="print-total-row">
            <span class="label">รวมทั้งหมด</span>
            <span class="count"><?= $summary['count'] ?> บิล</span>
            <span class="value"><?= formatPrice($summary['total']) ?></span>
        </div>
    </div>

    <!-- ส่วนท้าย -->
    <div class="print-footer">
        <div class="print-footer-left">
            <p>ผู้จัดทำรายงาน: <?= e(currentUser()['fullname'] ?? 'ผู้ดูแลระบบ') ?></p>
            <p>วันที่พิมพ์: <?= date('d/m/Y H:i') ?> น.</p>
        </div>
        <div class="print-signature">
            <div class="sign-line"></div>
            <p>ผู้ตรวจสอบ / ผู้จัดการ</p>
        </div>
    </div>

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
<div class="row g-3 mb-4 no-print">
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

<div class="row g-3 no-print">
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
