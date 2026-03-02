<?php
$pageTitle = 'คำสั่งซื้อ';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

// กรองสถานะ
$statusFilter = $_GET['status'] ?? '';
$where = '';
$params = [];
if ($statusFilter && array_key_exists($statusFilter, ORDER_STATUSES)) {
    $where = 'WHERE o.status = ?';
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("
    SELECT o.*, t.table_number,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o
    JOIN tables t ON o.table_id = t.id
    {$where}
    ORDER BY o.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-receipt"></i> คำสั่งซื้อ</h4>
</div>

<!-- กรองสถานะ -->
<div class="mb-3">
    <a href="orders.php" class="btn btn-sm <?= !$statusFilter ? 'btn-danger' : 'btn-outline-secondary' ?> me-1">ทั้งหมด</a>
    <?php foreach (ORDER_STATUSES as $key => $label): ?>
        <a href="orders.php?status=<?= $key ?>"
           class="btn btn-sm <?= $statusFilter === $key ? 'btn-danger' : 'btn-outline-secondary' ?> me-1">
            <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เลขออเดอร์</th>
                    <th>โต๊ะ</th>
                    <th>รายการ</th>
                    <th>ยอดรวม</th>
                    <th>สถานะ</th>
                    <th>เวลาสั่ง</th>
                    <th>ดู</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?= e($o['order_number']) ?></strong></td>
                    <td>โต๊ะ <?= e($o['table_number']) ?></td>
                    <td><?= $o['item_count'] ?> รายการ</td>
                    <td><?= formatPrice($o['total_amount']) ?></td>
                    <td><?= statusBadge($o['status']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info btn-action" onclick="viewOrder(<?= $o['id'] ?>)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">ไม่มีคำสั่งซื้อ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal ดูรายละเอียด -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดออเดอร์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetail">กำลังโหลด...</div>
        </div>
    </div>
</div>

<script>
function viewOrder(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderModal'));
    const body = document.getElementById('orderDetail');
    body.innerHTML = 'กำลังโหลด...';
    modal.show();

    fetch('../api/orders.php?id=' + orderId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const o = data.order;
                let html = `
                    <p><strong>ออเดอร์:</strong> #${o.order_number}</p>
                    <p><strong>โต๊ะ:</strong> ${o.table_number}</p>
                    <p><strong>เวลาสั่ง:</strong> ${o.created_at}</p>
                    <hr>
                    <table class="table table-sm">
                        <thead><tr><th>รายการ</th><th>ขนาด</th><th>เผ็ด</th><th>จำนวน</th><th>ราคา</th></tr></thead>
                        <tbody>
                `;
                o.items.forEach(item => {
                    const optText = item.options && item.options.length > 0
                        ? '<br><small class="text-muted">' + item.options.map(o => o.option_name + (parseFloat(o.extra_price) > 0 ? ' +฿' + parseFloat(o.extra_price).toFixed(0) : '')).join(', ') + '</small>'
                        : '';
                    html += `<tr>
                        <td>${item.menu_name}${optText}</td>
                        <td>${item.size === 'special' ? 'พิเศษ' : 'ธรรมดา'}</td>
                        <td>${'🌶️'.repeat(item.spice_level) || '-'}</td>
                        <td>${item.quantity}</td>
                        <td>฿${parseFloat(item.subtotal).toFixed(2)}</td>
                    </tr>`;
                });
                html += `</tbody></table>
                    <div class="text-end"><strong>รวม: ฿${parseFloat(o.total_amount).toFixed(2)}</strong></div>`;
                body.innerHTML = html;
            }
        });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
