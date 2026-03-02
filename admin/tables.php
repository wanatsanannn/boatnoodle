<?php
$pageTitle = 'จัดการโต๊ะ';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$tables = $pdo->query("SELECT * FROM tables ORDER BY CAST(table_number AS UNSIGNED), table_number")->fetchAll();

// แก้ไข
$editTable = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editTable = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-grid-3x3"></i> จัดการโต๊ะ</h4>
</div>

<div class="row">
    <!-- ฟอร์ม -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <?= $editTable ? 'แก้ไขโต๊ะ' : 'เพิ่มโต๊ะใหม่' ?>
            </div>
            <div class="card-body">
                <form method="POST" action="table_action.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editTable ? 'update' : 'create' ?>">
                    <?php if ($editTable): ?>
                        <input type="hidden" name="id" value="<?= $editTable['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">หมายเลขโต๊ะ</label>
                        <input type="text" name="table_number" class="form-control" required
                               value="<?= e($editTable['table_number'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">จำนวนที่นั่ง</label>
                        <input type="number" name="seats" class="form-control" min="1" required
                               value="<?= e($editTable['seats'] ?? '4') ?>">
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-check-lg"></i> <?= $editTable ? 'บันทึก' : 'เพิ่มโต๊ะ' ?>
                    </button>
                    <?php if ($editTable): ?>
                        <a href="tables.php" class="btn btn-secondary w-100 mt-2">ยกเลิก</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- ตารางโต๊ะ -->
    <div class="col-md-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>โต๊ะ</th>
                            <th>ที่นั่ง</th>
                            <th>สถานะ</th>
                            <th>QR Code</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $t): ?>
                        <tr>
                            <td><strong>โต๊ะ <?= e($t['table_number']) ?></strong></td>
                            <td><?= e($t['seats']) ?> ที่นั่ง</td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td>
                                <?php
                                $tokenStr = !empty($t['session_token']) ? '&token=' . $t['session_token'] : '';
                                $orderUrl = BASE_URL . '/order/?table=' . $t['table_number'] . $tokenStr;
                                $qrUrl = generateQRCodeURL($orderUrl);
                                ?>
                                <a href="<?= $qrUrl ?>" target="_blank" class="btn btn-sm btn-outline-dark btn-action" title="ดูคิวอาร์โค้ด">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-info btn-action"
                                        onclick="printQR('<?= e($t['table_number']) ?>', '<?= $qrUrl ?>')">
                                    <i class="bi bi-printer"></i> พิมพ์
                                </button>
                            </td>
                            <td>
                                <a href="tables.php?edit=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="แก้ไขโต๊ะ">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="table_action.php" class="d-inline" onsubmit="return confirm('ยืนยันระบบใหม่จะล้างคิวอาร์เดิมทิ้งทันที?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="reset_token">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-warning btn-action" title="สร้างคิวอาร์ใหม่">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                                <form method="POST" action="table_action.php" class="d-inline" onsubmit="return confirm('แน่ใจที่จะลบโต๊ะนี้ใช่ไหม?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-action btn-delete" title="ลบโต๊ะ">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Print QR Modal -->
<div id="printArea" style="display:none;">
    <div class="qr-print" id="qrPrintContent"></div>
</div>

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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
