<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ตรวจสอบว่า login แล้วหรือไม่
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * ดึงข้อมูลผู้ใช้ปัจจุบัน
 */
function currentUser() {
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'fullname' => $_SESSION['fullname'] ?? null,
        'role'     => $_SESSION['role'] ?? null,
    ];
}

/**
 * บังคับ login — ถ้ายังไม่ login ให้ redirect ไปหน้า login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('../admin/login.php');
    }
}

/**
 * ตรวจสอบ role — ถ้าไม่มีสิทธิ์ให้แสดงข้อผิดพลาด
 */
function requireRole(...$roles) {
    requireLogin();
    $user = currentUser();
    if (!in_array($user['role'], $roles)) {
        die('<div style="text-align:center;margin-top:50px;">
                <h2>ไม่มีสิทธิ์เข้าถึงหน้านี้</h2>
                <a href="../admin/index.php">กลับหน้าหลัก</a>
             </div>');
    }
}

/**
 * Login
 */
function loginUser($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role']     = $user['role'];
        return true;
    }
    return false;
}

/**
 * Logout
 */
function logoutUser() {
    session_destroy();
}
