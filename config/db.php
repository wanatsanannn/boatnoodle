<?php
// Timezone: ตั้งค่าให้ PHP และ MySQL ใช้เวลาตรงกัน
date_default_timezone_set('Asia/Bangkok');

// เชื่อมต่อฐานข้อมูล (PDO)
$db_host = 'sql111.infinityfree.com';
$db_name = 'if0_41288662_boat_noodle';
$db_user = 'if0_41288662';
$db_pass = 'oi3Hp1qVBPM';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    // ตั้ง timezone ของ MySQL session ให้ตรงกับ PHP
    $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    die('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $e->getMessage());
}
