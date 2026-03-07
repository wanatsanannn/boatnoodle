<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin', 'manager', 'chef');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ครัว — <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/kitchen.css?v=2" rel="stylesheet">
</head>
<body class="kitchen-body">

    <div class="kitchen-header">
        <div>
            <h1>🔥 ครัว — <?= SITE_NAME ?></h1>
        </div>
        <div>
            <span class="time" id="clock"></span>
            <a href="../admin/logout.php" class="btn btn-sm btn-outline-light ms-3">ออกจากระบบ</a>
        </div>
    </div>

    <div class="kitchen-grid" id="orderGrid">
        <div class="no-orders"><i class="bi bi-cup-hot"></i>กำลังโหลด...</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/kitchen.js"></script>
</body>
</html>
