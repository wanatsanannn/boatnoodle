<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');
verifyCSRF();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadImage($_FILES['image'], MENU_IMG_PATH);
        }

        $priceSpecial = trim($_POST['price_special']) !== '' ? (float)$_POST['price_special'] : null;

        $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, image, price_normal, price_special, has_spice_option, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            (int)$_POST['category_id'],
            trim($_POST['name']),
            trim($_POST['description']) ?: null,
            $image,
            (float)$_POST['price_normal'],
            $priceSpecial,
            (int)$_POST['has_spice_option'],
            $_POST['status']
        ]);
        $menuId = $pdo->lastInsertId();

        // บันทึกกลุ่มตัวเลือก
        $optionGroups = $_POST['option_groups'] ?? [];
        if (!empty($optionGroups)) {
            $stmtOg = $pdo->prepare("INSERT INTO menu_item_option_groups (menu_item_id, option_group_id) VALUES (?, ?)");
            foreach ($optionGroups as $ogId) {
                $stmtOg->execute([$menuId, (int)$ogId]);
            }
        }

        setFlash('success', 'เพิ่มเมนูสำเร็จ');
        break;

    case 'update':
        $id = (int)$_POST['id'];

        // ดึงข้อมูลเดิม
        $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();

        $image = $old['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImage = uploadImage($_FILES['image'], MENU_IMG_PATH);
            if ($newImage) {
                deleteImage($old['image'], MENU_IMG_PATH);
                $image = $newImage;
            }
        }

        $priceSpecial = trim($_POST['price_special']) !== '' ? (float)$_POST['price_special'] : null;

        $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ?, name = ?, description = ?, image = ?, price_normal = ?, price_special = ?, has_spice_option = ?, status = ? WHERE id = ?");
        $stmt->execute([
            (int)$_POST['category_id'],
            trim($_POST['name']),
            trim($_POST['description']) ?: null,
            $image,
            (float)$_POST['price_normal'],
            $priceSpecial,
            (int)$_POST['has_spice_option'],
            $_POST['status'],
            $id
        ]);

        // อัปเดตกลุ่มตัวเลือก
        $pdo->prepare("DELETE FROM menu_item_option_groups WHERE menu_item_id = ?")->execute([$id]);
        $optionGroups = $_POST['option_groups'] ?? [];
        if (!empty($optionGroups)) {
            $stmtOg = $pdo->prepare("INSERT INTO menu_item_option_groups (menu_item_id, option_group_id) VALUES (?, ?)");
            foreach ($optionGroups as $ogId) {
                $stmtOg->execute([$id, (int)$ogId]);
            }
        }

        setFlash('success', 'แก้ไขเมนูสำเร็จ');
        break;

    case 'toggle_status':
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE menu_items SET status = IF(status = 'available', 'sold_out', 'available') WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'เปลี่ยนสถานะสำเร็จ');
        break;

    case 'delete':
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        if ($old && $old['image']) {
            deleteImage($old['image'], MENU_IMG_PATH);
        }
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'ลบเมนูสำเร็จ');
        break;
}

redirect('menu.php');
