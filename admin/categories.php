<?php
$pageTitle = 'จัดการหมวดหมู่';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

// ดึงหมวดหมู่ทั้งหมด
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order, id")->fetchAll();

// แก้ไข: ดึงข้อมูลหมวดหมู่ที่จะแก้
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editCat = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-tag"></i> จัดการหมวดหมู่</h4>
</div>

<div class="row">
    <!-- ฟอร์มเพิ่ม/แก้ไข -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <?= $editCat ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่ใหม่' ?>
            </div>
            <div class="card-body">
                <form method="POST" action="category_action.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editCat ? 'update' : 'create' ?>">
                    <?php if ($editCat): ?>
                        <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">ชื่อหมวดหมู่</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= e($editCat['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลำดับ</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="<?= e($editCat['sort_order'] ?? '0') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editCat['status'] ?? '') === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                            <option value="inactive" <?= ($editCat['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-check-lg"></i> <?= $editCat ? 'บันทึกการแก้ไข' : 'เพิ่มหมวดหมู่' ?>
                    </button>
                    <?php if ($editCat): ?>
                        <a href="categories.php" class="btn btn-secondary w-100 mt-2">ยกเลิก</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- ตารางหมวดหมู่ -->
    <div class="col-md-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อหมวดหมู่</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= e($cat['sort_order']) ?></td>
                            <td><strong><?= e($cat['name']) ?></strong></td>
                            <td><?= statusBadge($cat['status']) ?></td>
                            <td>
                                <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="category_action.php" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">ยังไม่มีหมวดหมู่</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
