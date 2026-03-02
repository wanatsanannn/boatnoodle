<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');
verifyCSRF();

$action = $_POST['action'] ?? '';

switch ($action) {
    // === กลุ่มตัวเลือก ===
    case 'create_group':
        $stmt = $pdo->prepare("INSERT INTO option_groups (name, type, required, sort_order, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            trim($_POST['name']),
            $_POST['type'],
            (int)$_POST['required'],
            (int)$_POST['sort_order'],
            $_POST['status']
        ]);
        setFlash('success', 'เพิ่มกลุ่มตัวเลือกสำเร็จ');
        break;

    case 'update_group':
        $stmt = $pdo->prepare("UPDATE option_groups SET name = ?, type = ?, required = ?, sort_order = ?, status = ? WHERE id = ?");
        $stmt->execute([
            trim($_POST['name']),
            $_POST['type'],
            (int)$_POST['required'],
            (int)$_POST['sort_order'],
            $_POST['status'],
            (int)$_POST['id']
        ]);
        setFlash('success', 'แก้ไขกลุ่มตัวเลือกสำเร็จ');
        break;

    case 'delete_group':
        $stmt = $pdo->prepare("DELETE FROM option_groups WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
        setFlash('success', 'ลบกลุ่มตัวเลือกสำเร็จ');
        break;

    // === ตัวเลือกย่อย ===
    case 'create_choice':
        $stmt = $pdo->prepare("INSERT INTO option_choices (group_id, name, extra_price, is_default, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            (int)$_POST['group_id'],
            trim($_POST['name']),
            (float)$_POST['extra_price'],
            (int)$_POST['is_default'],
            (int)$_POST['sort_order']
        ]);
        setFlash('success', 'เพิ่มตัวเลือกสำเร็จ');
        break;

    case 'update_choice':
        $stmt = $pdo->prepare("UPDATE option_choices SET name = ?, extra_price = ?, is_default = ?, sort_order = ?, status = ? WHERE id = ?");
        $stmt->execute([
            trim($_POST['name']),
            (float)$_POST['extra_price'],
            (int)$_POST['is_default'],
            (int)$_POST['sort_order'],
            $_POST['status'],
            (int)$_POST['id']
        ]);
        setFlash('success', 'แก้ไขตัวเลือกสำเร็จ');
        break;

    case 'delete_choice':
        $stmt = $pdo->prepare("DELETE FROM option_choices WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
        setFlash('success', 'ลบตัวเลือกสำเร็จ');
        break;
}

redirect('options.php');
