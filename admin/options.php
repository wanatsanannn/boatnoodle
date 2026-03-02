<?php
$pageTitle = 'จัดการตัวเลือกเสริม';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

// ดึงกลุ่มตัวเลือกทั้งหมด
$groups = $pdo->query("SELECT * FROM option_groups ORDER BY sort_order, id")->fetchAll();

// ดึงตัวเลือกย่อยทั้งหมด จัดกลุ่มตาม group_id
$allChoices = $pdo->query("SELECT * FROM option_choices ORDER BY sort_order, id")->fetchAll();
$choicesByGroup = [];
foreach ($allChoices as $c) {
    $choicesByGroup[$c['group_id']][] = $c;
}

// แก้ไขกลุ่ม
$editGroup = null;
if (isset($_GET['edit_group'])) {
    $stmt = $pdo->prepare("SELECT * FROM option_groups WHERE id = ?");
    $stmt->execute([$_GET['edit_group']]);
    $editGroup = $stmt->fetch();
}

// แก้ไขตัวเลือกย่อย
$editChoice = null;
$editChoiceGroupId = null;
if (isset($_GET['edit_choice'])) {
    $stmt = $pdo->prepare("SELECT * FROM option_choices WHERE id = ?");
    $stmt->execute([$_GET['edit_choice']]);
    $editChoice = $stmt->fetch();
    $editChoiceGroupId = $editChoice['group_id'] ?? null;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-sliders"></i> จัดการตัวเลือกเสริม</h4>
</div>

<div class="row">
    <!-- ฟอร์มเพิ่ม/แก้ไขกลุ่มตัวเลือก -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <?= $editGroup ? 'แก้ไขกลุ่มตัวเลือก' : 'เพิ่มกลุ่มตัวเลือกใหม่' ?>
            </div>
            <div class="card-body">
                <form method="POST" action="option_action.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $editGroup ? 'update_group' : 'create_group' ?>">
                    <?php if ($editGroup): ?>
                        <input type="hidden" name="id" value="<?= $editGroup['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">ชื่อกลุ่ม <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= e($editGroup['name'] ?? '') ?>"
                               placeholder="เช่น ชนิดเส้น, ท็อปปิ้งเพิ่ม">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภท</label>
                        <select name="type" class="form-select">
                            <option value="single" <?= ($editGroup['type'] ?? '') === 'single' ? 'selected' : '' ?>>เลือกได้ 1 อย่าง (radio)</option>
                            <option value="multiple" <?= ($editGroup['type'] ?? '') === 'multiple' ? 'selected' : '' ?>>เลือกได้หลายอย่าง (checkbox)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ต้องเลือก</label>
                        <select name="required" class="form-select">
                            <option value="0" <?= ($editGroup['required'] ?? 0) == 0 ? 'selected' : '' ?>>ไม่บังคับ</option>
                            <option value="1" <?= ($editGroup['required'] ?? 0) == 1 ? 'selected' : '' ?>>บังคับเลือก</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">ลำดับ</label>
                                <input type="number" name="sort_order" class="form-control"
                                       value="<?= e($editGroup['sort_order'] ?? '0') ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">สถานะ</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($editGroup['status'] ?? '') === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                                    <option value="inactive" <?= ($editGroup['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-check-lg"></i> <?= $editGroup ? 'บันทึกการแก้ไข' : 'เพิ่มกลุ่ม' ?>
                    </button>
                    <?php if ($editGroup): ?>
                        <a href="options.php" class="btn btn-secondary w-100 mt-2">ยกเลิก</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- รายการกลุ่มตัวเลือก + ตัวเลือกย่อย -->
    <div class="col-md-8">
        <?php foreach ($groups as $g): ?>
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= e($g['name']) ?></strong>
                    <small class="text-muted ms-2">
                        (<?= $g['type'] === 'single' ? 'เลือก 1 อย่าง' : 'เลือกได้หลายอย่าง' ?>
                        <?= $g['required'] ? '• บังคับ' : '• ไม่บังคับ' ?>)
                    </small>
                    <?= statusBadge($g['status']) ?>
                </div>
                <div>
                    <a href="options.php?edit_group=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary btn-action">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" action="option_action.php" class="d-inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_group">
                        <input type="hidden" name="id" value="<?= $g['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger btn-action btn-delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ตัวเลือก</th>
                            <th>ราคาเพิ่ม</th>
                            <th>ค่าเริ่มต้น</th>
                            <th>ลำดับ</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($choicesByGroup[$g['id']])): ?>
                            <?php foreach ($choicesByGroup[$g['id']] as $c): ?>
                            <tr>
                                <td class="ps-3">
                                    <?php if ($editChoice && $editChoice['id'] == $c['id']): ?>
                                        <!-- Inline edit form -->
                                        <form method="POST" action="option_action.php" class="d-flex align-items-center gap-2">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="update_choice">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <input type="hidden" name="group_id" value="<?= $c['group_id'] ?>">
                                            <input type="text" name="name" class="form-control form-control-sm" value="<?= e($c['name']) ?>" required style="width:120px;">
                                    <?php else: ?>
                                        <?= e($c['name']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($editChoice && $editChoice['id'] == $c['id']): ?>
                                        <input type="number" name="extra_price" class="form-control form-control-sm" step="0.01" min="0" value="<?= $c['extra_price'] ?>" style="width:80px;">
                                    <?php else: ?>
                                        <?= $c['extra_price'] > 0 ? '+' . formatPrice($c['extra_price']) : '-' ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($editChoice && $editChoice['id'] == $c['id']): ?>
                                        <select name="is_default" class="form-select form-select-sm" style="width:70px;">
                                            <option value="0" <?= !$c['is_default'] ? 'selected' : '' ?>>ไม่</option>
                                            <option value="1" <?= $c['is_default'] ? 'selected' : '' ?>>ใช่</option>
                                        </select>
                                    <?php else: ?>
                                        <?= $c['is_default'] ? '<i class="bi bi-check-circle text-success"></i>' : '-' ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($editChoice && $editChoice['id'] == $c['id']): ?>
                                        <input type="number" name="sort_order" class="form-control form-control-sm" value="<?= $c['sort_order'] ?>" style="width:60px;">
                                    <?php else: ?>
                                        <?= $c['sort_order'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($editChoice && $editChoice['id'] == $c['id']): ?>
                                        <select name="status" class="form-select form-select-sm" style="width:90px;">
                                            <option value="active" <?= $c['status'] === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                                            <option value="inactive" <?= $c['status'] === 'inactive' ? 'selected' : '' ?>>ปิด</option>
                                        </select>
                                    <?php else: ?>
                                        <?= statusBadge($c['status']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($editChoice && $editChoice['id'] == $c['id']): ?>
                                            <button type="submit" class="btn btn-sm btn-success btn-action"><i class="bi bi-check-lg"></i></button>
                                            <a href="options.php" class="btn btn-sm btn-secondary btn-action"><i class="bi bi-x-lg"></i></a>
                                        </form>
                                    <?php else: ?>
                                        <a href="options.php?edit_choice=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary btn-action">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="option_action.php" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete_choice">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger btn-action btn-delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- ฟอร์มเพิ่มตัวเลือกใหม่ (แสดงใน row สุดท้าย) -->
                        <tr class="table-light">
                            <form method="POST" action="option_action.php">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="create_choice">
                                <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                                <td class="ps-3">
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="ชื่อตัวเลือก" required style="width:120px;">
                                </td>
                                <td>
                                    <input type="number" name="extra_price" class="form-control form-control-sm" step="0.01" min="0" value="0" style="width:80px;">
                                </td>
                                <td>
                                    <select name="is_default" class="form-select form-select-sm" style="width:70px;">
                                        <option value="0">ไม่</option>
                                        <option value="1">ใช่</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="sort_order" class="form-control form-control-sm" value="0" style="width:60px;">
                                </td>
                                <td colspan="2">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-plus-lg"></i> เพิ่ม
                                    </button>
                                </td>
                            </form>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($groups)): ?>
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    ยังไม่มีกลุ่มตัวเลือก — เพิ่มจากฟอร์มด้านซ้าย
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
