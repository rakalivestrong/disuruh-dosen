<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $user = fetchOne("SELECT * FROM users WHERE email = '$email'");
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        if ($user['role'] === 'super_admin') {
            header('Location: ../superadmin/dashboard.php');
        } elseif ($user['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../mahasiswa/dashboard.php');
        }
        exit;
    } else {
        $error = 'Email atau password salah!';
    }
}

// Proses register
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $nama = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $existing = fetchOne("SELECT id FROM users WHERE email = '$email'");
        if ($existing) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            query("INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$hashed', 'mahasiswa')");
            $success = 'Registrasi berhasil! Silakan login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registrasi — Sistem Beasiswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-bg">
        <div class="auth-bg-circle c1"></div>
        <div class="auth-bg-circle c2"></div>
        <div class="auth-bg-circle c3"></div>
    </div>

    <div class="auth-container">
        <!-- Logo / Brand -->
        <div class="auth-brand">
            <img src="../assets/img/logo.png" alt="Logo" class="auth-brand-logo">
            <h1>BeasiswaKu</h1>
            <p>Sistem Informasi Beasiswa Mahasiswa</p>
        </div>

        <!-- Tab Switcher -->
        <div class="auth-tabs">
            <button class="tab-btn active" id="tab-login" onclick="switchTab('login')">Masuk</button>
            <button class="tab-btn" id="tab-register" onclick="switchTab('register')">Daftar</button>
        </div>

        <!-- Alert -->
        <?php if ($error): ?>
        <div class="alert alert-error" id="alertBox">
            <span class="alert-icon">⚠️</span> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success" id="alertBox">
            <span class="alert-icon">✅</span> <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form class="auth-form" id="form-login" method="POST" action="">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="login-email">Email</label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input type="email" id="login-email" name="email" placeholder="nama@email.com" required>
                </div>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="login-password" name="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-pass" onclick="togglePassword('login-password', this)">👁️</button>
                </div>
            </div>
            <button type="submit" class="btn-primary btn-full">
                <span>Masuk</span>
                <span class="btn-arrow">→</span>
            </button>
            <p class="auth-footer-text">Belum punya akun? <a href="#" onclick="switchTab('register')">Daftar sekarang</a></p>
            <div class="demo-info">
                <strong>Demo Super Admin:</strong> superadmin@beasiswa.com / superadmin123<br>
                <strong>Demo Admin:</strong> admin@beasiswa.com / password
            </div>
        </form>

        <!-- Form Register -->
        <form class="auth-form hidden" id="form-register" method="POST" action="">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="reg-nama">Nama Lengkap</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" id="reg-nama" name="nama" placeholder="Nama lengkap kamu" required>
                </div>
            </div>
            <div class="form-group">
                <label for="reg-email">Email</label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input type="email" id="reg-email" name="email" placeholder="nama@email.com" required>
                </div>
            </div>
            <div class="form-group">
                <label for="reg-password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="reg-password" name="password" placeholder="Min. 6 karakter" required>
                    <button type="button" class="toggle-pass" onclick="togglePassword('reg-password', this)">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="reg-confirm">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="reg-confirm" name="confirm_password" placeholder="Ulangi password" required>
                </div>
            </div>
            <button type="submit" class="btn-primary btn-full">
                <span>Buat Akun</span>
                <span class="btn-arrow">→</span>
            </button>
            <p class="auth-footer-text">Sudah punya akun? <a href="#" onclick="switchTab('login')">Masuk di sini</a></p>
        </form>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>
