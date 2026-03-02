<?php
// ฟังก์ชันกลางของระบบ

/**
 * แสดงราคาในรูปแบบ ฿XX.XX
 */
function formatPrice($price) {
    return '฿' . number_format($price, 2);
}

/**
 * สร้างเลขออเดอร์จาก auto-increment ID เช่น 0001, 0002
 */
function generateOrderNumber($pdo, $orderId) {
    return str_pad($orderId, 4, '0', STR_PAD_LEFT);
}

/**
 * ป้องกัน XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * ตั้งค่าข้อความ flash
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * แสดงข้อความ flash
 */
function showFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $type = $f['type'];
        $msg = e($f['message']);
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$msg}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        unset($_SESSION['flash']);
    }
}

/**
 * อัปโหลดรูปภาพ
 * คืนค่าชื่อไฟล์ หรือ false ถ้าไม่สำเร็จ
 */
function uploadImage($file, $destination) {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_UPLOAD_SIZE) return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return false;

    $filename = uniqid('img_') . '.' . $ext;
    $path = $destination . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $filename;
    }
    return false;
}

/**
 * ลบรูปภาพ
 */
function deleteImage($filename, $directory) {
    $path = $directory . $filename;
    if ($filename && file_exists($path)) {
        unlink($path);
    }
}

/**
 * สร้าง QR Code URL (ใช้ QR Server API)
 */
function generateQRCodeURL($data, $size = 300) {
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
}

/**
 * แปลงสถานะเป็นสีปุ่ม Bootstrap
 */
function statusBadge($status) {
    $map = [
        'pending'   => 'warning',
        'cooking'   => 'info',
        'ready'     => 'success',
        'served'    => 'primary',
        'completed' => 'secondary',
        'cancelled' => 'danger',
        'available' => 'success',
        'sold_out'  => 'danger',
        'occupied'  => 'warning',
        'active'    => 'success',
        'inactive'  => 'secondary',
    ];
    $color = $map[$status] ?? 'secondary';
    $labels = array_merge(ORDER_STATUSES, [
        'available' => 'พร้อมขาย',
        'sold_out'  => 'ของหมด',
        'occupied'  => 'มีลูกค้า',
        'active'    => 'ใช้งาน',
        'inactive'  => 'ปิดใช้งาน',
    ]);
    $label = $labels[$status] ?? $status;
    return "<span class='badge bg-{$color}'>{$label}</span>";
}

/**
 * ตรวจสอบ CSRF token
 */
function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    $token = generateCSRF();
    return "<input type='hidden' name='csrf_token' value='{$token}'>";
}

function verifyCSRF() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die('CSRF token ไม่ถูกต้อง');
    }
}

/**
 * แปลง spice level เป็นข้อความ
 */
function spiceLabel($level) {
    return SPICE_LEVELS[$level] ?? 'ไม่ระบุ';
}

/**
 * แปลง size เป็นข้อความ
 */
function sizeLabel($size) {
    return $size === 'special' ? 'พิเศษ' : 'ธรรมดา';
}
