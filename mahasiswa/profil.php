<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
$userId = $_SESSION['user_id'];
$user = fetchOne("SELECT * FROM users WHERE id = $userId");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitize($_POST['nama']);
    $currentPass = $_POST['current_password'];
    $newPass = $_POST['new_password'];
    $confirmPass = $_POST['confirm_password'];

    // Update nama
    query("UPDATE users SET nama = '$nama' WHERE id = $userId");
    $_SESSION['user_nama'] = $nama;

    // Update password (optional)
    if (!empty($newPass)) {
        if (!password_verify($currentPass, $user['password'])) {
            $error = 'Password saat ini salah!';
        } elseif (strlen($newPass) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif ($newPass !== $confirmPass) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            query("UPDATE users SET password = '$hashed' WHERE id = $userId");
            $success = 'Profil dan password berhasil diperbarui!';
        }
    } else {
        $success = 'Profil berhasil diperbarui!';
    }

    // Refresh data
    $user = fetchOne("SELECT * FROM users WHERE id = $userId");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/form.css">
</head>
<body class="dashboard-page">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand"><a href="../index.php"><span class="brand-icon">🎓</span><span>BeasiswaKu</span></a></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">🏠</span><span>Dashboard</span></a>
            <a href="daftar_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Daftar Beasiswa</span></a>
            <a href="riwayat.php" class="nav-item"><span class="ni-icon">📋</span><span>Riwayat Pendaftaran</span></a>
            <a href="profil.php" class="nav-item active"><span class="ni-icon">👤</span><span>Profil Saya</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout"><span class="ni-icon">🚪</span><span>Keluar</span></a>
        </div>
    </aside>
    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info"><h2>Profil Saya</h2></div>
        </header>

        <div style="max-width:560px;">
            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

            <!-- Avatar -->
            <div class="dash-section" style="padding:32px; text-align:center; margin-bottom:20px;">
                <div style="width:80px;height:80px;border-radius:50%;background:var(--grad-primary);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:32px;margin:0 auto 16px;">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <h3 style="font-size:20px; font-weight:700;"><?= htmlspecialchars($user['nama']) ?></h3>
                <p style="color:var(--text-muted); font-size:14px;"><?= htmlspecialchars($user['email']) ?></p>
                <span class="status-badge status-diterima" style="margin-top:8px; display:inline-flex;">Mahasiswa</span>
            </div>

            <!-- Form -->
            <form class="app-form" method="POST">
                <div class="form-section">
                    <h4 class="fs-title">👤 Data Akun</h4>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.6;">
                        <small>Email tidak bisa diubah</small>
                    </div>
                </div>
                <div class="form-section">
                    <h4 class="fs-title">🔒 Ubah Password</h4>
                    <p class="fs-note">Kosongkan jika tidak ingin mengubah password</p>
                    <div class="form-group">
                        <label>Password Saat Ini</label>
                        <input type="password" name="current_password" placeholder="Password saat ini">
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" placeholder="Min. 6 karakter">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
