<?php
$pageTitle = 'รับชำระเงิน';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager', 'cashier');

// ดึงโต๊ะที่มีลูกค้า
$occupiedTables = $pdo->query("
    SELECT t.*,
           (SELECT COALESCE(SUM(o.total_amount), 0)
            FROM orders o
            WHERE o.table_id = t.id AND o.status IN ('pending','cooking','ready','served'))
           as bill_total,
           (SELECT COUNT(*)
            FROM orders o
            WHERE o.table_id = t.id AND o.status IN ('pending','cooking','ready','served'))
           as order_count
    FROM tables t
    WHERE t.status = 'occupied'
    ORDER BY CAST(t.table_number AS UNSIGNED)
")->fetchAll();

// ดูรายละเอียดโต๊ะ
$selectedTable = null;
$tableOrders = [];
if (isset($_GET['table'])) {
    $stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->execute([$_GET['table']]);
    $selectedTable = $stmt->fetch();

    if ($selectedTable) {
        $stmt = $pdo->prepare("
            SELECT o.*, GROUP_CONCAT(
                CONCAT(oi.quantity, '× ', m.name, ' (', IF(oi.size='special','พิเศษ','ธรรมดา'), ')',
                    COALESCE((SELECT CONCAT(' [', GROUP_CONCAT(oio.option_name SEPARATOR ', '), ']') FROM order_item_options oio WHERE oio.order_item_id = oi.id), ''),
                    ' ฿', FORMAT(oi.subtotal,2))
                SEPARATOR '||'
            ) as item_list
            FROM orders o
            JOIN order_items oi ON oi.order_id = o.id
            JOIN menu_items m ON oi.menu_item_id = m.id
            WHERE o.table_id = ? AND o.status IN ('pending','cooking','ready','served')
            GROUP BY o.id
            ORDER BY o.created_at
        ");
        $stmt->execute([$selectedTable['id']]);
        $tableOrders = $stmt->fetchAll();
    }
}

// บันทึกการชำระ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    verifyCSRF();
    $tableId = (int)$_POST['table_id'];
    $method = $_POST['method'];
    $totalAmount = (float)$_POST['total_amount'];

    try {
        $pdo->beginTransaction();

        // บันทึก payment
        $stmt = $pdo->prepare("INSERT INTO payments (table_id, total_amount, method, received_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tableId, $totalAmount, $method, currentUser()['id']]);

        // อัปเดตออเดอร์เป็น completed
        $pdo->prepare("UPDATE orders SET status = 'completed' WHERE table_id = ? AND status IN ('pending','cooking','ready','served')")
            ->execute([$tableId]);

        // อัปเดตโต๊ะเป็นว่าง
        $pdo->prepare("UPDATE tables SET status = 'available' WHERE id = ?")->execute([$tableId]);

        $pdo->commit();
        setFlash('success', 'บันทึกการชำระเงินสำเร็จ — โต๊ะ ' . $selectedTable['table_number']);
        redirect('payment.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="mb-4"><i class="bi bi-cash-stack"></i> รับชำระเงิน</h4>

<div class="row">
    <!-- รายการโต๊ะ -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white">โต๊ะที่มีลูกค้า</div>
            <div class="list-group list-group-flush">
                <?php foreach ($occupiedTables as $t): ?>
                    <a href="payment.php?table=<?= $t['id'] ?>"
                       class="list-group-item list-group-item-action <?= ($selectedTable && $selectedTable['id'] == $t['id']) ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <strong>โต๊ะ <?= e($t['table_number']) ?></strong>
                            <span><?= formatPrice($t['bill_total']) ?></span>
                        </div>
                        <small><?= $t['order_count'] ?> ออเดอร์</small>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($occupiedTables)): ?>
                    <div class="list-group-item text-center text-muted py-4">
                        ไม่มีโต๊ะที่มีลูกค้า
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- รายละเอียดบิล -->
    <div class="col-md-8">
        <?php if ($selectedTable && !empty($tableOrders)): ?>
            <?php $billTotal = array_sum(array_column($tableOrders, 'total_amount')); ?>
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">บิล — โต๊ะ <?= e($selectedTable['table_number']) ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($tableOrders as $o): ?>
                        <div class="mb-3 pb-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <strong>#<?= e($o['order_number']) ?></strong>
                                <span><?= date('H:i', strtotime($o['created_at'])) ?></span>
                            </div>
                            <?php foreach (explode('||', $o['item_list']) as $itemLine): ?>
                                <div class="small text-muted">• <?= e($itemLine) ?></div>
                            <?php endforeach; ?>
                            <div class="text-end"><strong><?= formatPrice($o['total_amount']) ?></strong></div>
                        </div>
                    <?php endforeach; ?>

                    <div class="border-top pt-3 mb-3">
                        <h4 class="text-end text-danger">รวมทั้งหมด: <?= formatPrice($billTotal) ?></h4>
                    </div>

                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="table_id" value="<?= $selectedTable['id'] ?>">
                        <input type="hidden" name="total_amount" value="<?= $billTotal ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">วิธีชำระเงิน</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="method" value="cash" id="payCash" checked>
                                    <label class="form-check-label" for="payCash">💵 เงินสด</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="method" value="promptpay" id="payPP">
                                    <label class="form-check-label" for="payPP">📱 PromptPay</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="pay" value="1" class="btn btn-success btn-lg w-100"
                                onclick="return confirm('ยืนยันรับชำระเงิน <?= formatPrice($billTotal) ?> ?')">
                            <i class="bi bi-check-circle"></i> บันทึกการชำระเงิน
                        </button>
                    </form>
                </div>
            </div>
        <?php elseif ($selectedTable): ?>
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    ไม่มีออเดอร์ค้างสำหรับโต๊ะนี้
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-arrow-left-circle" style="font-size:2rem;"></i>
                    <p class="mt-2">เลือกโต๊ะจากด้านซ้าย</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
