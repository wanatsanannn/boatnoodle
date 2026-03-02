<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager');
verifyCSRF();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        $stmt = $pdo->prepare("INSERT INTO categories (name, sort_order, status) VALUES (?, ?, ?)");
        $stmt->execute([
            trim($_POST['name']),
            (int)$_POST['sort_order'],
            $_POST['status']
        ]);
        setFlash('success', 'เพิ่มหมวดหมู่สำเร็จ');
        break;

    case 'update':
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, sort_order = ?, status = ? WHERE id = ?");
        $stmt->execute([
            trim($_POST['name']),
            (int)$_POST['sort_order'],
            $_POST['status'],
            (int)$_POST['id']
        ]);
        setFlash('success', 'แก้ไขหมวดหมู่สำเร็จ');
        break;

    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
        setFlash('success', 'ลบหมวดหมู่สำเร็จ');
        break;
}

redirect('categories.php');
