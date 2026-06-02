<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
$userId = $_SESSION['user_id'];
$beasiswaTersedia = fetchAll("
    SELECT b.* FROM beasiswa b 
    WHERE b.status = 'aktif' 
    AND b.id NOT IN (
        SELECT beasiswa_id FROM pendaftaran WHERE user_id = $userId
    )
    ORDER BY b.deadline ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Beasiswa — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php"><img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img"><span>BeasiswaKu</span></a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">🏠</span><span>Dashboard</span></a>
            <a href="daftar_beasiswa.php" class="nav-item active"><span class="ni-icon">🎓</span><span>Daftar Beasiswa</span></a>
            <a href="riwayat.php" class="nav-item"><span class="ni-icon">📋</span><span>Riwayat Pendaftaran</span></a>
            <a href="profil.php" class="nav-item"><span class="ni-icon">👤</span><span>Profil Saya</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout"><span class="ni-icon">🚪</span><span>Keluar</span></a>
        </div>
    </aside>
    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info"><h2>Beasiswa Tersedia</h2><p>Pilih Beasiswa sesuai kriteriamu</p></div>
        </header>
        <div class="beasiswa-available-grid">
            <?php if (empty($beasiswaTersedia)): ?>
            <div class="empty-dash" style="grid-column:1/-1">
                <div class="ed-icon">🎓</div>
                <p>Semua beasiswa sudah kamu daftarkan, atau belum ada beasiswa aktif.</p>
                <a href="dashboard.php" class="btn-primary">Kembali ke Dashboard</a>
            </div>
            <?php else: ?>
            <?php foreach ($beasiswaTersedia as $b): ?>
            <div class="bav-card">
                <div class="bav-top">
                    <div class="bav-icon">🎓</div>
                    <span class="bav-badge">Aktif</span>
                </div>
                <h3><?= htmlspecialchars($b['nama_beasiswa']) ?></h3>
                <p><?= htmlspecialchars(substr($b['deskripsi'], 0, 100)) ?>...</p>
                <div class="bav-meta">
                    <div><span>💰</span> <?= formatRupiah($b['nominal']) ?>/bln</div>
                    <div><span>📋</span> Kuota: <?= $b['kuota'] ?></div>
                    <div><span>📊</span> IPK Min: <?= number_format($b['nilai_minimum'], 2) ?></div>
                    <div><span>📅</span> <?= date('d M Y', strtotime($b['deadline'])) ?></div>
                </div>
                <?php if ($b['syarat']): ?>
                <div class="bav-syarat">
                    <strong>Syarat:</strong>
                    <p><?= nl2br(htmlspecialchars(substr($b['syarat'], 0, 150))) ?>...</p>
                </div>
                <?php endif; ?>
                <a href="form_daftar.php?id=<?= $b['id'] ?>" class="btn-primary btn-full">Daftar Sekarang →</a>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
