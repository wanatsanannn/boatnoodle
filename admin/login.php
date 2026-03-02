<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// ถ้า login อยู่แล้ว ไปหน้าหลัก
if (isLoggedIn()) {
    $role = currentUser()['role'];
    switch ($role) {
        case 'chef':
            redirect('../kitchen/index.php');
            break;
        case 'waiter':
            redirect('../staff/index.php');
            break;
        case 'cashier':
            redirect('../staff/payment.php');
            break;
        default:
            redirect('index.php');
            break;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        if (loginUser($pdo, $username, $password)) {
            $role = currentUser()['role'];
            switch ($role) {
                case 'chef':
                    redirect('../kitchen/index.php');
                    break;
                case 'waiter':
                    redirect('../staff/index.php');
                    break;
                case 'cashier':
                    redirect('../staff/payment.php');
                    break;
                default:
                    redirect('index.php');
                    break;
            }
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — <?= SITE_NAME ?></title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sarabun', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #d62828 0%, #e63946 50%, #f4a261 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, #d62828, #f4a261);
        }

        .login-card img {
            display: block;
            margin: 0 auto 1.5rem;
            height: 90px;
        }

        .login-card h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #d62828;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .form-label {
            font-weight: 600;
            color: #2b2d42;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #e63946;
            box-shadow: 0 0 0 0.25rem rgba(230, 57, 70, 0.25);
        }

        .btn-login {
            background: #e63946;
            border: none;
            width: 100%;
            padding: 0.85rem;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 12px;
            margin-top: 1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
        }

        .btn-login:hover {
            background: #d62828;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(230, 57, 70, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <img src="../assets/images/logo.png" alt="Logo">
        <h2><?= SITE_NAME ?></h2>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="border-radius: 12px; font-weight: 500;"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input type="text" name="username" class="form-control form-control-lg" required autofocus
                    value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" class="form-control form-control-lg" required>
            </div>
            <button type="submit" class="btn btn-danger btn-login">เข้าสู่ระบบ</button>
        </form>
    </div>
</body>

</html>