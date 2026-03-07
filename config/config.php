<?php
// ค่าคงที่ของระบบ
define('SITE_NAME', 'ก๋วยเตี๋ยวเรือชาม');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

// ตรวจสอบว่าระบบถูกรันอยู่ในโฟลเดอร์ /ก๋วยเตี๋ยวเรือชาม (เช่น ทดสอบใน XAMPP) หรือไม่
if (strpos($request_uri, '/ก๋วยเตี๋ยวเรือชาม') !== false || php_sapi_name() === 'cli') {
    // กำหนดให้ Fallback สำหรับ CLI เป็นแบบ Localhost ของ XAMPP
    define('BASE_URL', $protocol . '://' . $host . '/ก๋วยเตี๋ยวเรือชาม');
} else {
    // กรณีอัปโหลดขึ้นโฮสติ้งจริง (ไม่มีซับโฟลเดอร์)
    define('BASE_URL', $protocol . '://' . $host);
}

// เส้นทางไฟล์
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');
define('MENU_IMG_PATH', UPLOAD_PATH . 'menu/');
define('QR_PATH', UPLOAD_PATH . 'qrcodes/');

// ขนาดไฟล์อัปโหลดสูงสุด (2MB)
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);

// นามสกุลไฟล์ที่อนุญาต
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

// ระดับความเผ็ด
define('SPICE_LEVELS', [
    0 => 'ไม่เผ็ด',
    1 => 'เผ็ดน้อย',
    2 => 'เผ็ดกลาง',
    3 => 'เผ็ดมาก',
]);

// สถานะออเดอร์
define('ORDER_STATUSES', [
    'pending'   => 'รอรับออเดอร์',
    'cooking'   => 'กำลังเตรียมอาหาร',
    'ready'     => 'พร้อมเสิร์ฟ',
    'served'    => 'เสิร์ฟแล้ว',
    'completed' => 'เสร็จสิ้น',
    'cancelled' => 'ยกเลิก',
]);

// สิทธิ์ตาม role
define('ROLE_NAMES', [
    'admin'   => 'ผู้ดูแลระบบ',
    'manager' => 'หัวหน้างาน',
    'chef'    => 'พ่อครัว/แม่ครัว',
    'waiter'  => 'พนักงานเสิร์ฟ',
    'cashier' => 'แคชเชียร์',
]);
