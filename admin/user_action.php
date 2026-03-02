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
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            trim($_POST['username']),
            $hash,
            trim($_POST['fullname']),
            $_POST['role'],
            trim($_POST['phone']) ?: null,
            $_POST['status']
        ]);
        setFlash('success', 'เพิ่มผู้ใช้สำเร็จ');
        break;

    case 'update':
        $id = (int)$_POST['id'];
        $fields = "fullname = ?, role = ?, phone = ?, status = ?";
        $params = [
            trim($_POST['fullname']),
            $_POST['role'],
            trim($_POST['phone']) ?: null,
            $_POST['status'],
        ];

        if (!empty($_POST['password'])) {
            $fields = "password = ?, " . $fields;
            array_unshift($params, password_hash($_POST['password'], PASSWORD_DEFAULT));
        }

        $params[] = $id;
        $stmt = $pdo->prepare("UPDATE users SET {$fields} WHERE id = ?");
        $stmt->execute($params);
        setFlash('success', 'แก้ไขผู้ใช้สำเร็จ');
        break;

    case 'delete':
        $id = (int)$_POST['id'];
        if ($id != currentUser()['id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'ลบผู้ใช้สำเร็จ');
        }
        break;
}

redirect('users.php');
