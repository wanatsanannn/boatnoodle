<?php
$pageTitle = 'จัดการผู้ใช้';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$users = $pdo->query("SELECT * FROM users ORDER BY role, username")->fetchAll();

$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-people"></i> จัดการผู้ใช้</h4>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <?= $editUser ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้ใหม่' ?>
            </div>
            <div class="card-body">
                <form method="POST" action="user_action.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editUser ? 'update' : 'create' ?>">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" name="username" class="form-control" required
                               value="<?= e($editUser['username'] ?? '') ?>"
                               <?= $editUser ? 'readonly' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่าน <?= $editUser ? '(ว่างไว้ถ้าไม่เปลี่ยน)' : '' ?></label>
                        <input type="password" name="password" class="form-control"
                               <?= $editUser ? '' : 'required' ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" name="fullname" class="form-control" required
                               value="<?= e($editUser['fullname'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ตำแหน่ง</label>
                        <select name="role" class="form-select" required>
                            <?php foreach (ROLE_NAMES as $key => $label): ?>
                                <option value="<?= $key ?>"
                                    <?= ($editUser['role'] ?? '') === $key ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เบอร์โทร</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= e($editUser['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editUser['status'] ?? '') === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                            <option value="inactive" <?= ($editUser['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-check-lg"></i> <?= $editUser ? 'บันทึก' : 'เพิ่มผู้ใช้' ?>
                    </button>
                    <?php if ($editUser): ?>
                        <a href="users.php" class="btn btn-secondary w-100 mt-2">ยกเลิก</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ชื่อผู้ใช้</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ตำแหน่ง</th>
                            <th>เบอร์โทร</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?= e($u['username']) ?></strong></td>
                            <td><?= e($u['fullname']) ?></td>
                            <td><span class="badge bg-secondary"><?= e(ROLE_NAMES[$u['role']] ?? $u['role']) ?></span></td>
                            <td><?= e($u['phone'] ?: '-') ?></td>
                            <td><?= statusBadge($u['status']) ?></td>
                            <td>
                                <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($u['id'] != currentUser()['id']): ?>
                                <form method="POST" action="user_action.php" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
