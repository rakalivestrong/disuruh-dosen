<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Data laporan
$statsByBeasiswa = fetchAll("
    SELECT b.nama_beasiswa, 
           COUNT(p.id) as total,
           SUM(CASE WHEN p.status='diterima' THEN 1 ELSE 0 END) as diterima,
           SUM(CASE WHEN p.status='ditolak' THEN 1 ELSE 0 END) as ditolak,
           SUM(CASE WHEN p.status='menunggu' THEN 1 ELSE 0 END) as menunggu
    FROM beasiswa b
    LEFT JOIN pendaftaran p ON b.id = p.beasiswa_id
    GROUP BY b.id, b.nama_beasiswa
    ORDER BY total DESC
");

$totalAll = fetchOne("SELECT COUNT(*) as c FROM pendaftaran")['c'];
$totalDiterima = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status='diterima'")['c'];
$totalDitolak = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status='ditolak'")['c'];
$totalMenunggu = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status='menunggu'")['c'];

// Pendaftar per bulan (6 bulan terakhir)
$perBulan = fetchAll("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan,
           DATE_FORMAT(created_at, '%b %Y') as label,
           COUNT(*) as total
    FROM pendaftaran
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY bulan
    ORDER BY bulan ASC
");

$maxBulan = max(array_column($perBulan, 'total') ?: [1]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan — BeasiswaKu Admin</title>
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
            <a href="kelola_mahasiswa.php" class="nav-item"><span class="ni-icon">👥</span><span>Data Mahasiswa</span></a>
            <a href="laporan.php" class="nav-item active"><span class="ni-icon">📈</span><span>Laporan</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout"><span class="ni-icon">🚪</span><span>Keluar</span></a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info"><h2>Laporan & Statistik</h2><p>Ringkasan data sistem beasiswa</p></div>
            <button onclick="window.print()" class="btn-outline">🖨️ Print</button>
        </header>

        <!-- Stats -->
        <div class="stats-grid stats-6">
            <div class="stat-card stat-indigo">
                <div class="sc-icon">📝</div>
                <div class="sc-info"><span class="sc-num"><?= $totalAll ?></span><span class="sc-label">Total Pendaftar</span></div>
            </div>
            <div class="stat-card stat-green">
                <div class="sc-icon">✅</div>
                <div class="sc-info"><span class="sc-num"><?= $totalDiterima ?></span><span class="sc-label">Diterima</span></div>
            </div>
            <div class="stat-card stat-red">
                <div class="sc-icon">❌</div>
                <div class="sc-info"><span class="sc-num"><?= $totalDitolak ?></span><span class="sc-label">Ditolak</span></div>
            </div>
            <div class="stat-card stat-yellow">
                <div class="sc-icon">⏳</div>
                <div class="sc-info"><span class="sc-num"><?= $totalMenunggu ?></span><span class="sc-label">Menunggu</span></div>
            </div>
            <div class="stat-card stat-green">
                <div class="sc-icon">📊</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalAll > 0 ? round(($totalDiterima / $totalAll) * 100) : 0 ?>%</span>
                    <span class="sc-label">Tingkat Kelulusan</span>
                </div>
            </div>
            <div class="stat-card stat-blue">
                <div class="sc-icon">🎓</div>
                <div class="sc-info"><span class="sc-num"><?= count($statsByBeasiswa) ?></span><span class="sc-label">Program Beasiswa</span></div>
            </div>
        </div>

        <div class="chart-grid">
            <!-- Status Distribution -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3>Distribusi Status Pendaftaran</h3>
                    <p>Proporsi status dari total <?= $totalAll ?> pendaftaran</p>
                </div>
                <div class="bar-chart">
                    <?php
                    $items = [
                        ['Diterima', $totalDiterima, 'bar-success'],
                        ['Menunggu', $totalMenunggu, 'bar-warning'],
                        ['Ditolak', $totalDitolak, 'bar-danger'],
                    ];
                    foreach ($items as [$label, $val, $cls]):
                        $pct = $totalAll > 0 ? round(($val / $totalAll) * 100) : 0;
                    ?>
                    <div class="bar-item">
                        <span class="bar-label"><?= $label ?></span>
                        <div class="bar-track">
                            <div class="bar-fill <?= $cls ?>" style="width: <?= $pct ?>%">
                                <?= $pct > 10 ? "$val ($pct%)" : '' ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Per Bulan -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3>Tren Pendaftaran (6 Bulan)</h3>
                    <p>Jumlah pendaftar per bulan</p>
                </div>
                <?php if (!empty($perBulan)): ?>
                <div class="bar-chart">
                    <?php foreach ($perBulan as $b):
                        $pct = $maxBulan > 0 ? round(($b['total'] / $maxBulan) * 100) : 0;
                    ?>
                    <div class="bar-item">
                        <span class="bar-label"><?= $b['label'] ?></span>
                        <div class="bar-track">
                            <div class="bar-fill bar-primary" style="width: <?= $pct ?>%">
                                <?= $b['total'] ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color:var(--text-muted); font-size:14px;">Belum ada data.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Per Beasiswa -->
        <div class="dash-section">
            <div class="ds-header"><h3>Statistik per Beasiswa</h3></div>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Beasiswa</th>
                            <th>Total Pendaftar</th>
                            <th>Diterima</th>
                            <th>Ditolak</th>
                            <th>Menunggu</th>
                            <th>Tingkat Kelulusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statsByBeasiswa as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($s['nama_beasiswa']) ?></strong></td>
                            <td><?= $s['total'] ?></td>
                            <td><span class="status-badge status-diterima"><?= $s['diterima'] ?></span></td>
                            <td><span class="status-badge status-ditolak"><?= $s['ditolak'] ?></span></td>
                            <td><span class="status-badge status-menunggu"><?= $s['menunggu'] ?></span></td>
                            <td>
                                <?php $rate = $s['total'] > 0 ? round(($s['diterima'] / $s['total']) * 100) : 0; ?>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="flex:1; height:6px; background:rgba(255,255,255,0.08); border-radius:3px;">
                                        <div style="width:<?= $rate ?>%; height:100%; background:var(--grad-success); border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:12px; font-weight:700; color:#34d399;"><?= $rate ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
