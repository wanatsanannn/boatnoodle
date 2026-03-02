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
$status = $input['status'] ?? '';

$validStatuses = ['cooking', 'ready'];
if (!$orderId || !in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit;
}

try {
    // อัปเดตสถานะออเดอร์
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $orderId]);

    // อัปเดตสถานะรายการอาหารทั้งหมดในออเดอร์
    $stmt = $pdo->prepare("UPDATE order_items SET status = ? WHERE order_id = ?");
    $stmt->execute([$status, $orderId]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
