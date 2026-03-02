<?php
// API สำหรับลูกค้า
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// POST — สร้างออเดอร์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'create_order') {
        $tableNum = $input['table'] ?? '';
        $token = $input['token'] ?? '';
        $items = $input['items'] ?? [];

        if (!$tableNum || empty($items)) {
            echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบ']);
            exit;
        }

        // ตรวจสอบโต๊ะและโทเค็น
        $stmt = $pdo->prepare("SELECT id, session_token FROM tables WHERE table_number = ?");
        $stmt->execute([$tableNum]);
        $table = $stmt->fetch();
        if (!$table) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบโต๊ะ']);
            exit;
        }
        if (empty($table['session_token']) || $token !== $table['session_token']) {
            echo json_encode(['success' => false, 'message' => 'QR Code หมดอายุ กรุณาสแกนใหม่']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // คำนวณยอดรวม
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += (float)$item['subtotal'];
            }

            // สร้างออเดอร์ (ใส่ชื่อชั่วคราวก่อน แล้วอัปเดตด้วยเลขจาก ID)
            $stmt = $pdo->prepare("INSERT INTO orders (table_id, order_number, total_amount) VALUES (?, '', ?)");
            $stmt->execute([$table['id'], $totalAmount]);
            $orderId = $pdo->lastInsertId();
            $orderNumber = generateOrderNumber($pdo, $orderId);
            $pdo->prepare("UPDATE orders SET order_number = ? WHERE id = ?")->execute([$orderNumber, $orderId]);

            // บันทึกรายการอาหาร
            $stmtItem = $pdo->prepare("
                INSERT INTO order_items (order_id, menu_item_id, quantity, size, spice_level, unit_price, subtotal, note)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtOpt = $pdo->prepare("
                INSERT INTO order_item_options (order_item_id, option_choice_id, option_name, extra_price)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $stmtItem->execute([
                    $orderId,
                    (int)$item['menu_id'],
                    (int)$item['quantity'],
                    $item['size'] ?? 'normal',
                    (int)($item['spice_level'] ?? 0),
                    (float)$item['unit_price'],
                    (float)$item['subtotal'],
                    $item['note'] ?? null
                ]);
                $orderItemId = $pdo->lastInsertId();

                // บันทึกตัวเลือกเสริม
                if (!empty($item['options'])) {
                    foreach ($item['options'] as $opt) {
                        $stmtOpt->execute([
                            $orderItemId,
                            (int)$opt['choice_id'],
                            $opt['name'],
                            (float)$opt['extra_price']
                        ]);
                    }
                }
            }

            // อัปเดตสถานะโต๊ะ
            $pdo->prepare("UPDATE tables SET status = 'occupied' WHERE id = ?")->execute([$table['id']]);

            $pdo->commit();
            echo json_encode(['success' => true, 'order_number' => $orderNumber]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
        exit;
    }

    // POST — ยกเลิกออเดอร์
    if ($action === 'cancel_order') {
        $orderNum = $input['order_number'] ?? '';
        if (!$orderNum) {
            echo json_encode(['success' => false, 'message' => 'ไม่ระบุหมายเลขออเดอร์']);
            exit;
        }

        // ตรวจสอบสถานะออเดอร์
        $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNum]);
        $order = $stmt->fetch();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบออเดอร์']);
            exit;
        }

        if ($order['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถยกเลิกได้ เนื่องจากออเดอร์ถูกดำเนินการแล้ว']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$order['id']]);
            $pdo->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?")->execute([$order['id']]);
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
        }
        exit;
    }
}

// GET — ดึงสถานะออเดอร์
$action = $_GET['action'] ?? '';

if ($action === 'order_status') {
    $orderNum = $_GET['order'] ?? '';

    // ดึงสถานะออเดอร์
    $stmtOrder = $pdo->prepare("SELECT status FROM orders WHERE order_number = ?");
    $stmtOrder->execute([$orderNum]);
    $orderRow = $stmtOrder->fetch();
    $orderStatus = $orderRow ? $orderRow['status'] : null;

    $stmt = $pdo->prepare("
        SELECT oi.*, m.name as menu_name
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN menu_items m ON oi.menu_item_id = m.id
        WHERE o.order_number = ?
        ORDER BY oi.id
    ");
    $stmt->execute([$orderNum]);
    $items = $stmt->fetchAll();

    // ดึง options ของแต่ละ order_item
    foreach ($items as &$item) {
        $stmtOpt = $pdo->prepare("SELECT option_name, extra_price FROM order_item_options WHERE order_item_id = ?");
        $stmtOpt->execute([$item['id']]);
        $item['options'] = $stmtOpt->fetchAll();
    }
    unset($item);

    echo json_encode(['success' => true, 'items' => $items, 'order_status' => $orderStatus]);
    exit;
}

// GET — ดึงออเดอร์ทั้งหมดของโต๊ะ
if ($action === 'table_orders') {
    $tableNum = $_GET['table'] ?? '';
    if (!$tableNum) {
        echo json_encode(['success' => false, 'message' => 'ไม่ระบุหมายเลขโต๊ะ']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at,
               TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_ago
        FROM orders o
        JOIN tables t ON o.table_id = t.id
        WHERE t.table_number = ? AND o.status IN ('pending','cooking','ready','served')
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$tableNum]);
    $orders = $stmt->fetchAll();

    foreach ($orders as &$order) {
        // ดึงรายการอาหารในแต่ละออเดอร์
        $stmtItems = $pdo->prepare("
            SELECT oi.quantity, oi.size, oi.spice_level, oi.status as item_status,
                   m.name as menu_name
            FROM order_items oi
            JOIN menu_items m ON oi.menu_item_id = m.id
            WHERE oi.order_id = ?
            ORDER BY oi.id
        ");
        $stmtItems->execute([$order['id']]);
        $order['items'] = $stmtItems->fetchAll();

        // แปลงเวลา
        $mins = (int)$order['minutes_ago'];
        if ($mins < 1) $order['time_ago'] = 'เมื่อสักครู่';
        elseif ($mins < 60) $order['time_ago'] = $mins . ' นาทีที่แล้ว';
        else $order['time_ago'] = floor($mins / 60) . ' ชม. ที่แล้ว';
    }
    unset($order);

    echo json_encode(['success' => true, 'orders' => $orders]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
