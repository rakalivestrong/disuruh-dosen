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

// Beasiswa tersedia dengan validasi gap 1 tahun:
// - Exclude beasiswa yang aktif (menunggu/diterima) kapanpun
// - Exclude beasiswa yang sudah didaftar di tahun ini (walau ditolak)
$tahunIni = date('Y');
$daftarBeasiswaAktifId = array_column(
    array_filter($pendaftaran, fn($p) => in_array($p['status'], ['menunggu', 'diterima'])),
    'beasiswa_id'
);
$daftarBeasiswaTahunIniId = array_column(
    array_filter($pendaftaran, fn($p) => date('Y', strtotime($p['created_at'])) == $tahunIni),
    'beasiswa_id'
);
$excludeIds = array_unique(array_merge($daftarBeasiswaAktifId, $daftarBeasiswaTahunIniId));
$excludeClause = !empty($excludeIds) ? 'AND id NOT IN (' . implode(',', $excludeIds) . ')' : '';
$beasiswaTersedia = fetchAll("SELECT * FROM beasiswa WHERE status = 'aktif' $excludeClause ORDER BY deadline ASC");

// Notifikasi (semua, bukan hanya yang belum dibaca)
$notif = fetchAll("SELECT * FROM notifikasi WHERE user_id = $userId ORDER BY created_at DESC LIMIT 15");
$notifCount = fetchOne("SELECT COUNT(*) as c FROM notifikasi WHERE user_id = $userId AND dibaca = 0")['c'];

// Mark as read via AJAX
if (isset($_GET['mark_read']) && $_GET['mark_read'] === '1') {
    query("UPDATE notifikasi SET dibaca = 1 WHERE user_id = $userId");
    echo json_encode(['success' => true]);
    exit;
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
    <link rel="stylesheet" href="../assets/css/notif.css">
</head>
<body class="dashboard-page">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php">
                <img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img">
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
                <!-- Notification Bell -->
                <div class="notif-wrapper" id="notifWrapper">
                    <button class="notif-badge" id="notifBtn" onclick="toggleNotif()" title="Notifikasi">
                        <span>🔔</span>
                        <?php if ($notifCount > 0): ?>
                        <span class="badge-count" id="badgeCount"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </button>
                    <!-- Dropdown Panel -->
                    <div class="notif-dropdown hidden" id="notifDropdown">
                        <div class="notif-dd-header">
                            <span>🔔 Notifikasi</span>
                            <?php if ($notifCount > 0): ?>
                            <button onclick="markAllRead()" class="notif-mark-btn">Tandai semua dibaca</button>
                            <?php endif; ?>
                        </div>
                        <div class="notif-dd-body">
                            <?php if (empty($notif)): ?>
                            <div class="notif-empty">📭 Belum ada notifikasi</div>
                            <?php else: ?>
                            <?php foreach ($notif as $n): ?>
                            <div class="notif-dd-item <?= $n['dibaca'] ? '' : 'unread' ?>">
                                <div class="notif-dot"></div>
                                <div class="notif-content">
                                    <p><?= htmlspecialchars($n['pesan']) ?></p>
                                    <span><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="user-avatar"><?= strtoupper(substr($userNama, 0, 1)) ?></div>
            </div>
        </header>

        <!-- Notif section removed: now using dropdown -->


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
