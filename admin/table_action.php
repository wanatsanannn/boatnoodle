<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');
verifyCSRF();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        $stmt = $pdo->prepare("INSERT INTO tables (table_number, seats) VALUES (?, ?)");
        $stmt->execute([
            trim($_POST['table_number']),
            (int)$_POST['seats']
        ]);
        setFlash('success', 'เพิ่มโต๊ะสำเร็จ');
        break;

    case 'update':
        $stmt = $pdo->prepare("UPDATE tables SET table_number = ?, seats = ? WHERE id = ?");
        $stmt->execute([
            trim($_POST['table_number']),
            (int)$_POST['seats'],
            (int)$_POST['id']
        ]);
        setFlash('success', 'แก้ไขโต๊ะสำเร็จ');
        break;

    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM tables WHERE id = ?");
        $stmt->execute([(int)$_POST['id']]);
        setFlash('success', 'ลบโต๊ะสำเร็จ');
        break;
}

redirect('tables.php');
