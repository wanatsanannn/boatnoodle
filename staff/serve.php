<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'ไม่ได้เข้าสู่ระบบ']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($input['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit;
}

try {
    $pdo->prepare("UPDATE orders SET status = 'served' WHERE id = ?")->execute([$orderId]);
    $pdo->prepare("UPDATE order_items SET status = 'served' WHERE order_id = ?")->execute([$orderId]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
