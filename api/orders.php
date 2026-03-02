<?php
// API กลาง — ดึงข้อมูลออเดอร์
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

// ดูรายละเอียดออเดอร์เดี่ยว
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT o.*, t.table_number
        FROM orders o
        JOIN tables t ON o.table_id = t.id
        WHERE o.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch();

    if ($order) {
        $stmt = $pdo->prepare("
            SELECT oi.*, m.name as menu_name
            FROM order_items oi
            JOIN menu_items m ON oi.menu_item_id = m.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();

        // ดึง options ของแต่ละ item
        foreach ($order['items'] as &$oItem) {
            $stmtOpt = $pdo->prepare("SELECT option_name, extra_price FROM order_item_options WHERE order_item_id = ?");
            $stmtOpt->execute([$oItem['id']]);
            $oItem['options'] = $stmtOpt->fetchAll();
        }
        unset($oItem);
        echo json_encode(['success' => true, 'order' => $order]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบออเดอร์']);
    }
    exit;
}

// ดึงออเดอร์ตามสถานะ (สำหรับครัว/เสิร์ฟ)
$statusFilter = $_GET['status'] ?? 'pending,cooking';
$statuses = explode(',', $statusFilter);
$placeholders = implode(',', array_fill(0, count($statuses), '?'));

$stmt = $pdo->prepare("
    SELECT o.*, t.table_number,
           TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_ago
    FROM orders o
    JOIN tables t ON o.table_id = t.id
    WHERE o.status IN ({$placeholders})
    ORDER BY o.created_at ASC
");
$stmt->execute($statuses);
$orders = $stmt->fetchAll();

// เพิ่มรายการอาหารในแต่ละออเดอร์
foreach ($orders as &$order) {
    $stmt = $pdo->prepare("
        SELECT oi.*, m.name as menu_name
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll();

    // ดึง options ของแต่ละ item
    foreach ($order['items'] as &$item) {
        $stmtOpt = $pdo->prepare("SELECT option_name, extra_price FROM order_item_options WHERE order_item_id = ?");
        $stmtOpt->execute([$item['id']]);
        $item['options'] = $stmtOpt->fetchAll();
    }
    unset($item);
    // แปลงเวลา
    $mins = (int)$order['minutes_ago'];
    if ($mins < 1) $order['time_ago'] = 'เมื่อสักครู่';
    elseif ($mins < 60) $order['time_ago'] = $mins . ' นาทีที่แล้ว';
    else $order['time_ago'] = floor($mins / 60) . ' ชม. ที่แล้ว';
}

echo json_encode(['success' => true, 'orders' => $orders]);
