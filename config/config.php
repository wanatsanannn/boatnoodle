<?php
// ค่าคงที่ของระบบ
define('SITE_NAME', 'ก๋วยเตี๋ยวเรือชาม');

// ตรวจสอบว่าเป็น Localhost หรือ Server จริง เพื่อกำหนด URL อัตโนมัติ
$is_localhost = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1');

if ($is_localhost) {
    define('BASE_URL', 'http://localhost/ก๋วยเตี๋ยวเรือชาม');
} else {
    // โดเมนของ InfinityFree เช่น http://boatnoodle.infinityfreeapp.com
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST']);
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
