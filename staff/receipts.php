<?php
$pageTitle = 'ประวัติใบเสร็จ';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager', 'cashier');

// ดึงข้อมูลการชำระเงิน เรียงจากล่าสุุดไปเก่า
$payments = $pdo->query("
    SELECT p.*, t.table_number, u.fullname as cashier_name
    FROM payments p
    LEFT JOIN tables t ON p.table_id = t.id
    LEFT JOIN users u ON p.received_by = u.id
    ORDER BY p.created_at DESC
    LIMIT 300
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-receipt-cutoff"></i> ประวัติใบเสร็จ</h4>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>วัน/เวลา</th>
                        <th>โต๊ะ</th>
                        <th>วิธีรับเงิน</th>
                        <th>พนักงานรับเงิน</th>
                        <th class="text-end">ยอดรวม</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                            <td><span class="badge bg-secondary">โต๊ะ <?= e($p['table_number'] ?? 'N/A') ?></span></td>
                            <td>
                                <?php if ($p['method'] === 'cash'): ?>
                                    <span class="text-success"><i class="bi bi-cash"></i> เงินสด</span>
                                <?php else: ?>
                                    <span class="text-primary"><i class="bi bi-phone"></i> โอนเงิน(PP)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($p['cashier_name']) ?></td>
                            <td class="text-end fw-bold text-danger"><?= formatPrice($p['total_amount']) ?></td>
                            <td class="text-center">
                                <button onclick="printReceipt(<?= $p['id'] ?>)" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-printer"></i> พิมพ์ใบเสร็จ
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">ยังไม่มีประวัติการรับชำระเงิน</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function printReceipt(paymentId) {
    const w = window.open('receipt_print.php?id=' + paymentId, '_blank', 'width=450,height=700');
    // receipt_print.php will handle window.print() internally
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
