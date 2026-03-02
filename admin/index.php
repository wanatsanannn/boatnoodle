<?php
$pageTitle = 'แดชบอร์ด';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

// สถิติวันนี้
$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$todayOrders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM payments WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$todaySales = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM tables WHERE status = 'occupied'");
$tablesOccupied = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','cooking')");
$pendingOrders = $stmt->fetchColumn();

// ออเดอร์ล่าสุด 10 รายการ
$stmt = $pdo->query("
    SELECT o.*, t.table_number
    FROM orders o
    JOIN tables t ON o.table_id = t.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recentOrders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="mb-4"><i class="bi bi-speedometer2"></i> แดชบอร์ด</h4>

<!-- สถิติ -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card position-relative" style="background:linear-gradient(135deg, #c0392b 0%, #e74c3c 50%, #ff6b6b 100%);">
            <i class="bi bi-receipt"></i>
            <div class="stat-number"><?= $todayOrders ?></div>
            <div class="stat-label">ออเดอร์วันนี้</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card position-relative" style="background:linear-gradient(135deg, #1e8449 0%, #27ae60 50%, #2ecc71 100%);">
            <i class="bi bi-cash-stack"></i>
            <div class="stat-number"><?= formatPrice($todaySales) ?></div>
            <div class="stat-label">ยอดขายวันนี้</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card position-relative" style="background:linear-gradient(135deg, #d35400 0%, #e67e22 50%, #f39c12 100%);">
            <i class="bi bi-fire"></i>
            <div class="stat-number"><?= $pendingOrders ?></div>
            <div class="stat-label">ออเดอร์ค้าง</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card position-relative" style="background:linear-gradient(135deg, #1a5276 0%, #2980b9 50%, #3498db 100%);">
            <i class="bi bi-grid-3x3"></i>
            <div class="stat-number"><?= $tablesOccupied ?></div>
            <div class="stat-label">โต๊ะที่ใช้งาน</div>
        </div>
    </div>
</div>

<!-- ออเดอร์ล่าสุด -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> ออเดอร์ล่าสุด</span>
        <a href="orders.php" class="btn btn-sm btn-outline-danger">ดูทั้งหมด</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เลขออเดอร์</th>
                    <th>โต๊ะ</th>
                    <th>ยอด</th>
                    <th>สถานะ</th>
                    <th>เวลา</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $o): ?>
                <tr>
                    <td><strong>#<?= e($o['order_number']) ?></strong></td>
                    <td>โต๊ะ <?= e($o['table_number']) ?></td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td><?= date('H:i', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentOrders)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีออเดอร์</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
