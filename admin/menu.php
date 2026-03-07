<?php
$pageTitle = 'จัดการเมนูอาหาร';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

// กรองตามหมวดหมู่
$catFilter = $_GET['category'] ?? '';
$where = '';
$params = [];
if ($catFilter) {
    $where = 'WHERE m.category_id = ?';
    $params[] = (int)$catFilter;
}

$stmt = $pdo->prepare("
    SELECT m.*, c.name AS category_name
    FROM menu_items m
    JOIN categories c ON m.category_id = c.id
    {$where}
    ORDER BY c.sort_order, m.name
");
$stmt->execute($params);
$items = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>รายการอาหาร</h4>
    <a href="menu_form.php" class="btn btn-danger"><i class="bi bi-plus-lg"></i> เพิ่มเมนู</a>
</div>

<!-- กรองหมวดหมู่ -->
<div class="mb-3">
    <a href="menu.php" class="btn btn-sm <?= !$catFilter ? 'btn-danger' : 'btn-outline-secondary' ?> me-1">ทั้งหมด</a>
    <?php foreach ($categories as $cat): ?>
        <a href="menu.php?category=<?= $cat['id'] ?>"
           class="btn btn-sm <?= $catFilter == $cat['id'] ? 'btn-danger' : 'btn-outline-secondary' ?> me-1">
            <?= e($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>รูป</th>
                    <th>ชื่อเมนู</th>
                    <th>หมวดหมู่</th>
                    <th>ราคา</th>
                    <th>ราคาพิเศษ</th>
                    <th>เผ็ด</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php if ($item['image']): ?>
                            <img src="../assets/uploads/menu/<?= e($item['image']) ?>" class="menu-thumb" alt="">
                        <?php else: ?>
                            <div class="menu-thumb bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= e($item['name']) ?></strong>
                        <?php if ($item['description']): ?>
                            <br><small class="text-muted"><?= e($item['description']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= e($item['category_name']) ?></td>
                    <td><?= formatPrice($item['price_normal']) ?></td>
                    <td><?= $item['price_special'] ? formatPrice($item['price_special']) : '-' ?></td>
                    <td><?= $item['has_spice_option'] ? '<i class="bi bi-check-circle text-success"></i>' : '-' ?></td>
                    <td>
                        <?php if ($item['status'] === 'available'): ?>
                            <span class="badge bg-success">พร้อมขาย</span>
                        <?php else: ?>
                            <?= statusBadge($item['status']) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- ปุ่มเปลี่ยนสถานะ -->
                        <form method="POST" action="menu_action.php" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button class="btn btn-sm <?= $item['status'] === 'available' ? 'btn-outline-warning' : 'btn-outline-success' ?> btn-action"
                                    title="<?= $item['status'] === 'available' ? 'เปลี่ยนเป็นของหมด' : 'เปลี่ยนเป็นพร้อมขาย' ?>">
                                <i class="bi <?= $item['status'] === 'available' ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                            </button>
                        </form>
                        <a href="menu_form.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary btn-action">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="menu_action.php" class="d-inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">ยังไม่มีรายการอาหาร</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
