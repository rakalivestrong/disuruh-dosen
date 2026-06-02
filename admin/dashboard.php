<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Statistik
$totalPendaftar = fetchOne("SELECT COUNT(*) as c FROM pendaftaran")['c'];
$totalDiterima = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'diterima'")['c'];
$totalMenunggu = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'menunggu'")['c'];
$totalDitolak = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'ditolak'")['c'];
$totalBeasiswa = fetchOne("SELECT COUNT(*) as c FROM beasiswa WHERE status = 'aktif'")['c'];
$totalMahasiswa = fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'mahasiswa'")['c'];

// Pendaftaran terbaru
$pendaftaranTerbaru = fetchAll("
    SELECT p.*, u.nama as mahasiswa_nama, u.email, b.nama_beasiswa
    FROM pendaftaran p
    JOIN users u ON p.user_id = u.id
    JOIN beasiswa b ON p.beasiswa_id = b.id
    ORDER BY p.created_at DESC
    LIMIT 10
");

$adminNama = $_SESSION['user_nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="dashboard-page">
    <!-- Sidebar -->
    <aside class="sidebar admin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php">
                <img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img">
                <span>BeasiswaKu</span>
            </a>
            <span class="admin-badge">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
                <span class="ni-icon">📊</span><span>Dashboard</span>
            </a>
            <a href="kelola_beasiswa.php" class="nav-item">
                <span class="ni-icon">🎓</span><span>Kelola Beasiswa</span>
            </a>
            <a href="kelola_pendaftaran.php" class="nav-item">
                <span class="ni-icon">📋</span><span>Kelola Pendaftaran</span>
            </a>
            <a href="kelola_mahasiswa.php" class="nav-item">
                <span class="ni-icon">👥</span><span>Data Mahasiswa</span>
            </a>
            <a href="laporan.php" class="nav-item">
                <span class="ni-icon">📈</span><span>Laporan</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout">
                <span class="ni-icon">🚪</span><span>Keluar</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info">
                <h2>Dashboard Admin</h2>
                <p>Halo, <?= htmlspecialchars($adminNama) ?> &bull; <?= date('d F Y') ?></p>
            </div>
            <div class="dash-header-actions">
                <div class="user-avatar admin-av"><?= strtoupper(substr($adminNama, 0, 1)) ?></div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid stats-6">
            <div class="stat-card stat-blue">
                <div class="sc-icon">👥</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalMahasiswa ?></span>
                    <span class="sc-label">Total Mahasiswa</span>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <div class="sc-icon">🎓</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalBeasiswa ?></span>
                    <span class="sc-label">Beasiswa Aktif</span>
                </div>
            </div>
            <div class="stat-card stat-indigo">
                <div class="sc-icon">📝</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalPendaftar ?></span>
                    <span class="sc-label">Total Pendaftar</span>
                </div>
            </div>
            <div class="stat-card stat-yellow">
                <div class="sc-icon">⏳</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalMenunggu ?></span>
                    <span class="sc-label">Menunggu Review</span>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="sc-icon">✅</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalDiterima ?></span>
                    <span class="sc-label">Diterima</span>
                </div>
            </div>
            <div class="stat-card stat-red">
                <div class="sc-icon">❌</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalDitolak ?></span>
                    <span class="sc-label">Ditolak</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="kelola_beasiswa.php?action=tambah" class="qa-card">
                <span class="qa-icon">➕</span>
                <span>Tambah Beasiswa</span>
            </a>
            <a href="kelola_pendaftaran.php?status=menunggu" class="qa-card">
                <span class="qa-icon">⏳</span>
                <span>Review Pending (<?= $totalMenunggu ?>)</span>
            </a>
            <a href="laporan.php" class="qa-card">
                <span class="qa-icon">📊</span>
                <span>Lihat Laporan</span>
            </a>
        </div>

        <!-- Tabel Pendaftaran Terbaru -->
        <div class="dash-section">
            <div class="ds-header">
                <h3>Pendaftaran Terbaru</h3>
                <a href="kelola_pendaftaran.php" class="btn-sm-primary">Lihat Semua</a>
            </div>
            <?php if (empty($pendaftaranTerbaru)): ?>
            <div class="empty-dash">
                <div class="ed-icon">📭</div>
                <p>Belum ada pendaftaran masuk.</p>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mahasiswa</th>
                            <th>Beasiswa</th>
                            <th>IPK</th>
                            <th>Tgl Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendaftaranTerbaru as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($p['mahasiswa_nama']) ?></strong>
                                <small><?= htmlspecialchars($p['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($p['nama_beasiswa']) ?></td>
                            <td><?= number_format($p['ipk'], 2) ?></td>
                            <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $p['status'] ?>">
                                    <?php
                                    $labels = ['menunggu' => '⏳ Menunggu', 'diterima' => '✅ Diterima', 'ditolak' => '❌ Ditolak'];
                                    echo $labels[$p['status']] ?? $p['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_pendaftaran.php?id=<?= $p['id'] ?>" class="btn-sm">Review</a>
                            </td>
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
