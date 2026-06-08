<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$mahasiswaList = fetchAll("
    SELECT u.*, 
    COUNT(p.id) as total_daftar,
    SUM(CASE WHEN p.status='diterima' THEN 1 ELSE 0 END) as total_diterima
    FROM users u
    LEFT JOIN pendaftaran p ON u.id = p.user_id
    WHERE u.role = 'mahasiswa'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa — BeasiswaKu Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="dashboard-page">
    <aside class="sidebar admin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php"><img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img"><span>BeasiswaKu</span></a>
            <span class="admin-badge">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">📊</span><span>Dashboard</span></a>
            <a href="kelola_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Kelola Beasiswa</span></a>
            <a href="kelola_pendaftaran.php" class="nav-item"><span class="ni-icon">📋</span><span>Kelola Pendaftaran</span></a>
            <a href="kelola_mahasiswa.php" class="nav-item active"><span class="ni-icon">👥</span><span>Data Mahasiswa</span></a>
            <a href="laporan.php" class="nav-item"><span class="ni-icon">📈</span><span>Laporan</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout"><span class="ni-icon">🚪</span><span>Keluar</span></a>
        </div>
    </aside>
    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info">
                <h2>Data Mahasiswa</h2>
                <p>Total: <?= count($mahasiswaList) ?> mahasiswa terdaftar</p>
            </div>
        </header>
        <div class="dash-section">
            <div class="ds-header"><h3>Daftar Mahasiswa</h3></div>
            <?php if (empty($mahasiswaList)): ?>
            <div class="empty-dash"><div class="ed-icon">👥</div><p>Belum ada mahasiswa terdaftar.</p></div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tgl Daftar</th>
                            <th>Total Pengajuan</th>
                            <th>Beasiswa Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswaList as $i => $m): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:var(--grad-primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">
                                        <?= strtoupper(substr($m['nama'], 0, 1)) ?>
                                    </div>
                                    <strong><?= htmlspecialchars($m['nama']) ?></strong>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($m['email']) ?></td>
                            <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-menunggu"><?= $m['total_daftar'] ?> pengajuan</span>
                            </td>
                            <td>
                                <?php if ($m['total_diterima'] > 0): ?>
                                <span class="status-badge status-diterima">✅ <?= $m['total_diterima'] ?> beasiswa</span>
                                <?php else: ?>
                                <span style="color:var(--text-dim); font-size:13px;">—</span>
                                <?php endif; ?>
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
