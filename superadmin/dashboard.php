<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Statistik sistem keseluruhan
$totalUsers      = fetchOne("SELECT COUNT(*) as c FROM users WHERE role != 'super_admin'")['c'];
$totalMahasiswa  = fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'mahasiswa'")['c'];
$totalAdmin      = fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'admin'")['c'];
$totalBeasiswa   = fetchOne("SELECT COUNT(*) as c FROM beasiswa")['c'];
$totalPendaftar  = fetchOne("SELECT COUNT(*) as c FROM pendaftaran")['c'];
$totalDiterima   = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'diterima'")['c'];
$totalMenunggu   = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'menunggu'")['c'];
$totalDitolak    = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'ditolak'")['c'];

// Dana beasiswa tersalurkan
$danaTersalurkan = fetchOne("
    SELECT COALESCE(SUM(b.nominal), 0) as total
    FROM pendaftaran p
    JOIN beasiswa b ON p.beasiswa_id = b.id
    WHERE p.status = 'diterima'
")['total'];

// Aktivitas terbaru (semua role)
$aktivitasTerbaru = fetchAll("
    SELECT u.nama, u.role, u.email, u.created_at as waktu, 'user_baru' as jenis
    FROM users u ORDER BY u.created_at DESC LIMIT 5
");

$superNama = $_SESSION['user_nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/superadmin.css">
</head>
<body class="dashboard-page superadmin-page">
    <!-- Sidebar -->
    <aside class="sidebar superadmin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php">
                <img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img">
                <span>BeasiswaKu</span>
            </a>
            <span class="sa-badge">SUPER ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group-label">Dashboard</div>
            <a href="dashboard.php" class="nav-item active">
                <span class="ni-icon">🏠</span><span>Overview</span>
            </a>
            <div class="nav-group-label">Manajemen User</div>
            <a href="kelola_admin.php" class="nav-item">
                <span class="ni-icon">🛡️</span><span>Kelola Admin</span>
            </a>
            <a href="kelola_users.php" class="nav-item">
                <span class="ni-icon">👥</span><span>Semua Pengguna</span>
            </a>
            <div class="nav-group-label">Sistem</div>
            <a href="kelola_beasiswa.php" class="nav-item">
                <span class="ni-icon">🎓</span><span>Kelola Beasiswa</span>
            </a>
            <a href="laporan.php" class="nav-item">
                <span class="ni-icon">📊</span><span>Laporan Sistem</span>
            </a>
            <a href="../admin/dashboard.php" class="nav-item">
                <span class="ni-icon">🔗</span><span>Panel Admin</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sa-user-info">
                <div class="sa-avatar"><?= strtoupper(substr($superNama, 0, 1)) ?></div>
                <div>
                    <div class="sa-name"><?= htmlspecialchars($superNama) ?></div>
                    <div class="sa-role">Super Administrator</div>
                </div>
            </div>
            <a href="../auth/logout.php" class="nav-item nav-logout">
                <span class="ni-icon">🚪</span><span>Keluar</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dash-header sa-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info">
                <h2>Super Admin Dashboard</h2>
                <p>Selamat datang, <strong><?= htmlspecialchars($superNama) ?></strong> &bull; <?= date('d F Y') ?></p>
            </div>
            <div class="sa-header-badge">
                <span class="crown-icon">👑</span>
                <span>Full Control</span>
            </div>
        </header>

        <!-- Alert Banner -->
        <?php if ($totalMenunggu > 0): ?>
        <div class="sa-alert-banner">
            <span>⚠️</span>
            <span>Ada <strong><?= $totalMenunggu ?> pendaftaran</strong> yang menunggu review admin.</span>
            <a href="../admin/kelola_pendaftaran.php?status=menunggu">Lihat →</a>
        </div>
        <?php endif; ?>

        <!-- Stats Grid Row 1 -->
        <div class="sa-section-title">📈 Statistik Sistem</div>
        <div class="stats-grid stats-5">
            <div class="stat-card stat-blue">
                <div class="sc-icon">👥</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalUsers ?></span>
                    <span class="sc-label">Total Pengguna</span>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <div class="sc-icon">🎓</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalBeasiswa ?></span>
                    <span class="sc-label">Program Beasiswa</span>
                </div>
            </div>
            <div class="stat-card stat-indigo">
                <div class="sc-icon">📝</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalPendaftar ?></span>
                    <span class="sc-label">Total Pendaftar</span>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="sc-icon">✅</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalDiterima ?></span>
                    <span class="sc-label">Diterima</span>
                </div>
            </div>
            <div class="stat-card stat-yellow">
                <div class="sc-icon">⏳</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalMenunggu ?></span>
                    <span class="sc-label">Menunggu</span>
                </div>
            </div>
        </div>

        <!-- Dana Tersalurkan -->
        <div class="sa-dana-card">
            <div class="sa-dana-icon">💰</div>
            <div>
                <div class="sa-dana-label">Total Dana Beasiswa Tersalurkan</div>
                <div class="sa-dana-amount"><?= formatRupiah($danaTersalurkan) ?> / bulan</div>
            </div>
            <div class="sa-dana-badge">Kumulatif dari <?= $totalDiterima ?> penerima</div>
        </div>

        <!-- Quick Actions -->
        <div class="sa-section-title">⚡ Aksi Cepat</div>
        <div class="quick-actions sa-quick-actions">
            <a href="kelola_admin.php?action=tambah" class="qa-card sa-qa">
                <span class="qa-icon">➕</span>
                <span>Tambah Admin</span>
            </a>
            <a href="kelola_users.php" class="qa-card sa-qa">
                <span class="qa-icon">👥</span>
                <span>Kelola Pengguna</span>
            </a>
            <a href="kelola_beasiswa.php" class="qa-card sa-qa">
                <span class="qa-icon">🎓</span>
                <span>Kelola Beasiswa</span>
            </a>
            <a href="laporan.php" class="qa-card sa-qa">
                <span class="qa-icon">📊</span>
                <span>Lihat Laporan</span>
            </a>
            <a href="../admin/kelola_pendaftaran.php" class="qa-card sa-qa">
                <span class="qa-icon">📋</span>
                <span>Kelola Pendaftaran</span>
            </a>
        </div>

        <!-- User Terbaru -->
        <div class="dash-section">
            <div class="ds-header">
                <h3>👤 Pengguna Terbaru Bergabung</h3>
                <a href="kelola_users.php" class="btn-sm-primary">Lihat Semua</a>
            </div>
            <?php if (empty($aktivitasTerbaru)): ?>
            <div class="empty-dash"><div class="ed-icon">👥</div><p>Belum ada aktivitas.</p></div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bergabung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aktivitasTerbaru as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="mini-avatar"><?= strtoupper(substr($u['nama'], 0, 1)) ?></div>
                                    <strong><?= htmlspecialchars($u['nama']) ?></strong>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php
                                $roleMap = [
                                    'super_admin' => ['👑 Super Admin', 'sa-role-badge sa'],
                                    'admin'        => ['🛡️ Admin', 'sa-role-badge admin'],
                                    'mahasiswa'    => ['🎓 Mahasiswa', 'sa-role-badge mhs'],
                                ];
                                [$label, $cls] = $roleMap[$u['role']] ?? [$u['role'], 'sa-role-badge'];
                                echo "<span class=\"$cls\">$label</span>";
                                ?>
                            </td>
                            <td><?= date('d M Y', strtotime($u['waktu'])) ?></td>
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
