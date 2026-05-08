<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$userId = $_SESSION['user_id'];
$userNama = $_SESSION['user_nama'];

// Data pendaftaran user
$pendaftaran = fetchAll("
    SELECT p.*, b.nama_beasiswa, b.nominal, b.deadline 
    FROM pendaftaran p 
    JOIN beasiswa b ON p.beasiswa_id = b.id 
    WHERE p.user_id = $userId 
    ORDER BY p.created_at DESC
");

// Beasiswa tersedia (belum didaftar user)
$daftarBeasiswaId = array_column($pendaftaran, 'beasiswa_id');
$excludeClause = !empty($daftarBeasiswaId) ? 'AND id NOT IN (' . implode(',', $daftarBeasiswaId) . ')' : '';
$beasiswaTersedia = fetchAll("SELECT * FROM beasiswa WHERE status = 'aktif' $excludeClause ORDER BY deadline ASC");

// Notifikasi belum dibaca
$notif = fetchAll("SELECT * FROM notifikasi WHERE user_id = $userId AND dibaca = 0 ORDER BY created_at DESC");
$notifCount = count($notif);

// Mark all as read
if ($notifCount > 0) {
    query("UPDATE notifikasi SET dibaca = 1 WHERE user_id = $userId");
}

// Statistik
$totalDaftar = count($pendaftaran);
$diterima = count(array_filter($pendaftaran, fn($p) => $p['status'] === 'diterima'));
$menunggu = count(array_filter($pendaftaran, fn($p) => $p['status'] === 'menunggu'));
$ditolak = count(array_filter($pendaftaran, fn($p) => $p['status'] === 'ditolak'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php">
                <span class="brand-icon">🎓</span>
                <span>BeasiswaKu</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
                <span class="ni-icon">🏠</span>
                <span>Dashboard</span>
            </a>
            <a href="daftar_beasiswa.php" class="nav-item">
                <span class="ni-icon">🎓</span>
                <span>Daftar Beasiswa</span>
            </a>
            <a href="riwayat.php" class="nav-item">
                <span class="ni-icon">📋</span>
                <span>Riwayat Pendaftaran</span>
            </a>
            <a href="profil.php" class="nav-item">
                <span class="ni-icon">👤</span>
                <span>Profil Saya</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout">
                <span class="ni-icon">🚪</span>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info">
                <h2>Halo, <?= htmlspecialchars($userNama) ?> 👋</h2>
                <p><?= date('l, d F Y') ?></p>
            </div>
            <div class="dash-header-actions">
                <?php if ($notifCount > 0): ?>
                <div class="notif-badge">
                    <span>🔔</span>
                    <span class="badge-count"><?= $notifCount ?></span>
                </div>
                <?php endif; ?>
                <div class="user-avatar"><?= strtoupper(substr($userNama, 0, 1)) ?></div>
            </div>
        </header>

        <!-- Notifikasi -->
        <?php if (!empty($notif)): ?>
        <div class="notif-bar">
            <?php foreach ($notif as $n): ?>
            <div class="notif-item">
                <span>🔔</span>
                <?= htmlspecialchars($n['pesan']) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="sc-icon">📝</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalDaftar ?></span>
                    <span class="sc-label">Total Pendaftaran</span>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="sc-icon">✅</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $diterima ?></span>
                    <span class="sc-label">Diterima</span>
                </div>
            </div>
            <div class="stat-card stat-yellow">
                <div class="sc-icon">⏳</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $menunggu ?></span>
                    <span class="sc-label">Menunggu</span>
                </div>
            </div>
            <div class="stat-card stat-red">
                <div class="sc-icon">❌</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $ditolak ?></span>
                    <span class="sc-label">Ditolak</span>
                </div>
            </div>
        </div>

        <!-- Riwayat Pendaftaran -->
        <div class="dash-section">
            <div class="ds-header">
                <h3>Riwayat Pendaftaran</h3>
                <a href="daftar_beasiswa.php" class="btn-sm-primary">+ Daftar Beasiswa</a>
            </div>
            <?php if (empty($pendaftaran)): ?>
            <div class="empty-dash">
                <div class="ed-icon">📭</div>
                <p>Kamu belum mendaftar beasiswa apapun.</p>
                <a href="daftar_beasiswa.php" class="btn-primary">Lihat Beasiswa Tersedia</a>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Beasiswa</th>
                            <th>Dana</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendaftaran as $p): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($p['nama_beasiswa']) ?></strong>
                                <small>Didaftar: <?= date('d M Y', strtotime($p['created_at'])) ?></small>
                            </td>
                            <td><?= formatRupiah($p['nominal']) ?>/bln</td>
                            <td><?= date('d M Y', strtotime($p['deadline'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $p['status'] ?>">
                                    <?php
                                    $statusLabel = [
                                        'menunggu' => '⏳ Menunggu',
                                        'diterima' => '✅ Diterima',
                                        'ditolak' => '❌ Ditolak'
                                    ];
                                    echo $statusLabel[$p['status']] ?? $p['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_pendaftaran.php?id=<?= $p['id'] ?>" class="btn-sm">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Beasiswa Tersedia -->
        <?php if (!empty($beasiswaTersedia)): ?>
        <div class="dash-section">
            <div class="ds-header">
                <h3>Beasiswa Tersedia</h3>
            </div>
            <div class="beasiswa-list">
                <?php foreach (array_slice($beasiswaTersedia, 0, 3) as $b): ?>
                <div class="bl-card">
                    <div class="blc-left">
                        <div class="blc-icon">🎓</div>
                        <div>
                            <h4><?= htmlspecialchars($b['nama_beasiswa']) ?></h4>
                            <p>Deadline: <?= date('d M Y', strtotime($b['deadline'])) ?> &bull; Kuota: <?= $b['kuota'] ?> orang</p>
                        </div>
                    </div>
                    <div class="blc-right">
                        <span class="blc-nominal"><?= formatRupiah($b['nominal']) ?>/bln</span>
                        <a href="form_daftar.php?id=<?= $b['id'] ?>" class="btn-sm-primary">Daftar</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
