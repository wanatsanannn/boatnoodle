<?php
// API แจ้งเตือน — เช็คจำนวนรายการใหม่
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$type = $_GET['type'] ?? '';

switch ($type) {
    case 'kitchen':
        // จำนวนออเดอร์ pending
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        echo json_encode(['count' => (int)$stmt->fetchColumn()]);
        break;

    case 'serve':
        // จำนวนออเดอร์ ready
        $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'ready'");
        echo json_encode(['count' => (int)$stmt->fetchColumn()]);
        break;

    default:
        echo json_encode(['count' => 0]);
}
