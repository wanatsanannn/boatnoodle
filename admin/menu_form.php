<?php
$pageTitle = 'เพิ่ม/แก้ไขเมนู';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');

// โหลดข้อมูลถ้าเป็นการแก้ไข
$item = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch();
    if (!$item) { redirect('menu.php'); }
}

$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order")->fetchAll();

// ดึงกลุ่มตัวเลือกที่ใช้งานอยู่
$optionGroups = $pdo->query("SELECT * FROM option_groups WHERE status = 'active' ORDER BY sort_order")->fetchAll();

// ดึงกลุ่มตัวเลือกที่เชื่อมกับเมนูนี้
$linkedGroups = [];
if ($item) {
    $stmt = $pdo->prepare("SELECT option_group_id FROM menu_item_option_groups WHERE menu_item_id = ?");
    $stmt->execute([$item['id']]);
    $linkedGroups = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <a href="menu.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> กลับ</a>
    <h4 class="mt-2"><?= $item ? 'แก้ไขเมนู: ' . e($item['name']) : 'เพิ่มเมนูใหม่' ?></h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="menu_action.php" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="<?= $item ? 'update' : 'create' ?>">
                    <?php if ($item): ?>
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">ชื่อเมนู <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?= e($item['name'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"
                                            <?= ($item['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= e($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย</label>
                        <textarea name="description" class="form-control" rows="2"><?= e($item['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">ราคา(บาท) <span class="text-danger">*</span></label>
                                <input type="number" name="price_normal" class="form-control" step="0.01" min="0" required
                                       value="<?= e($item['price_normal'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">ราคาพิเศษ (บาท)</label>
                                <input type="number" name="price_special" class="form-control" step="0.01" min="0"
                                       value="<?= e($item['price_special'] ?? '') ?>"
                                       placeholder="ว่างไว้ถ้าไม่มี">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">ตัวเลือกความเผ็ด</label>
                                <select name="has_spice_option" class="form-select">
                                    <option value="1" <?= ($item['has_spice_option'] ?? 1) == 1 ? 'selected' : '' ?>>มี</option>
                                    <option value="0" <?= ($item['has_spice_option'] ?? 1) == 0 ? 'selected' : '' ?>>ไม่มี</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รูปภาพ (jpg, png, webp — ไม่เกิน 2MB)</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <?php if ($item && $item['image']): ?>
                            <div class="mt-2">
                                <img src="../assets/uploads/menu/<?= e($item['image']) ?>" height="80" class="rounded">
                                <small class="text-muted ms-2">รูปปัจจุบัน — อัปโหลดใหม่เพื่อเปลี่ยน</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="available" <?= ($item['status'] ?? '') === 'available' ? 'selected' : '' ?>>พร้อมขาย</option>
                            <option value="sold_out" <?= ($item['status'] ?? '') === 'sold_out' ? 'selected' : '' ?>>ของหมด</option>
                        </select>
                    </div>

                    <?php if (!empty($optionGroups)): ?>
                    <div class="mb-3">
                        <label class="form-label">ตัวเลือกเสริม <small class="text-muted">(เลือกกลุ่มที่ต้องการใช้กับเมนูนี้)</small></label>
                        <?php foreach ($optionGroups as $og): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="option_groups[]"
                                       value="<?= $og['id'] ?>" id="og_<?= $og['id'] ?>"
                                       <?= in_array($og['id'], $linkedGroups) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="og_<?= $og['id'] ?>">
                                    <?= e($og['name']) ?>
                                    <small class="text-muted">(<?= $og['type'] === 'single' ? 'เลือก 1' : 'เลือกหลายอย่าง' ?><?= $og['required'] ? ' • บังคับ' : '' ?>)</small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-lg"></i> <?= $item ? 'บันทึกการแก้ไข' : 'เพิ่มเมนู' ?>
                    </button>
                    <a href="menu.php" class="btn btn-secondary">ยกเลิก</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
