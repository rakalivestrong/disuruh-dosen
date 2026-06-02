<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
$userId = $_SESSION['user_id'];

$riwayat = fetchAll("
    SELECT p.*, b.nama_beasiswa, b.nominal, b.deadline
    FROM pendaftaran p
    JOIN beasiswa b ON p.beasiswa_id = b.id
    WHERE p.user_id = $userId
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pendaftaran — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand"><a href="../index.php"><img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img"><span>BeasiswaKu</span></a></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">🏠</span><span>Dashboard</span></a>
            <a href="daftar_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Daftar Beasiswa</span></a>
            <a href="riwayat.php" class="nav-item active"><span class="ni-icon">📋</span><span>Riwayat Pendaftaran</span></a>
            <a href="profil.php" class="nav-item"><span class="ni-icon">👤</span><span>Profil Saya</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout"><span class="ni-icon">🚪</span><span>Keluar</span></a>
        </div>
    </aside>
    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info"><h2>Riwayat Pendaftaran</h2><p>Total: <?= count($riwayat) ?> pendaftaran</p></div>
        </header>
        <div class="dash-section">
            <?php if (empty($riwayat)): ?>
            <div class="empty-dash">
                <div class="ed-icon">📭</div>
                <p>Kamu belum pernah mendaftar beasiswa.</p>
                <a href="daftar_beasiswa.php" class="btn-primary">Lihat Beasiswa Tersedia</a>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Beasiswa</th><th>NIM</th><th>IPK</th>
                            <th>Dana/Bln</th><th>Deadline</th><th>Tgl Daftar</th><th>Status</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riwayat as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($r['nama_beasiswa']) ?></strong></td>
                            <td><?= htmlspecialchars($r['nim']) ?></td>
                            <td><?= number_format($r['ipk'], 2) ?></td>
                            <td><?= formatRupiah($r['nominal']) ?></td>
                            <td><?= date('d M Y', strtotime($r['deadline'])) ?></td>
                            <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $r['status'] ?>">
                                    <?php $labels = ['menunggu'=>'⏳ Menunggu','diterima'=>'✅ Diterima','ditolak'=>'❌ Ditolak']; echo $labels[$r['status']]; ?>
                                </span>
                            </td>
                            <td><a href="detail_pendaftaran.php?id=<?= $r['id'] ?>" class="btn-sm">Detail</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
