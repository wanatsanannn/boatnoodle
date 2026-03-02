<?php
// ส่วนหัวของหน้าเว็บ (ส่วน admin/staff)
$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? SITE_NAME) ?> — <?= e(SITE_NAME) ?></title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <?php if (isset($extraCSS))
        echo $extraCSS; ?>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../admin/index.php">
                <img src="../assets/images/logo.png" alt="Logo" height="32" class="me-2">
                <?= e(SITE_NAME) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav me-auto">
                    <?php if (in_array($user['role'], ['admin', 'manager'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="../admin/index.php">
                                <i class="bi bi-speedometer2"></i> แดชบอร์ด
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['categories', 'menu', 'menu_form', 'options']) ? 'active' : '' ?>"
                                href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-egg-fried"></i> จัดการเมนู
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../admin/categories.php">หมวดหมู่</a></li>
                                <li><a class="dropdown-item" href="../admin/menu.php">รายการอาหาร</a></li>
                                <li><a class="dropdown-item" href="../admin/options.php">ตัวเลือกเสริม</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'tables' ? 'active' : '' ?>" href="../admin/tables.php">
                                <i class="bi bi-grid-3x3"></i> โต๊ะ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'orders' ? 'active' : '' ?>" href="../admin/orders.php">
                                <i class="bi bi-receipt"></i> ออเดอร์
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= str_contains($currentPage, 'report') ? 'active' : '' ?>"
                                href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-graph-up"></i> รายงาน
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../admin/reports_sales.php">ยอดขาย</a></li>
                                <li><a class="dropdown-item" href="../admin/reports_popular.php">เมนูขายดี</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" href="../admin/users.php">
                                <i class="bi bi-people"></i> ผู้ใช้
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($user['role'] === 'chef'): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="../kitchen/index.php">
                                <i class="bi bi-fire"></i> ครัว
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($user['role'] === 'waiter'): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="../staff/index.php">
                                <i class="bi bi-bell"></i> เสิร์ฟ
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($user['role'] === 'cashier'): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="../staff/payment.php">
                                <i class="bi bi-cash-stack"></i> ชำระเงิน
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="navbar-nav">
                    <span class="nav-link text-light">
                        <i class="bi bi-person-circle"></i> <?= e($user['fullname']) ?>
                        <small class="opacity-75">(<?= e(ROLE_NAMES[$user['role']] ?? $user['role']) ?>)</small>
                    </span>
                    <a class="nav-link text-warning" href="../admin/logout.php">
                        <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3 px-4">
        <?php showFlash(); ?>