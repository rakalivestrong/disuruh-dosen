<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Stats keseluruhan
$totalUsers     = fetchOne("SELECT COUNT(*) as c FROM users WHERE role != 'super_admin'")['c'];
$totalAdmin     = fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'admin'")['c'];
$totalMahasiswa = fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'mahasiswa'")['c'];
$totalBeasiswa  = fetchOne("SELECT COUNT(*) as c FROM beasiswa")['c'];
$totalPendaftar = fetchOne("SELECT COUNT(*) as c FROM pendaftaran")['c'];
$totalDiterima  = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'diterima'")['c'];
$totalDitolak   = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'ditolak'")['c'];
$totalMenunggu  = fetchOne("SELECT COUNT(*) as c FROM pendaftaran WHERE status = 'menunggu'")['c'];

$danaTersalurkan = fetchOne("
    SELECT COALESCE(SUM(b.nominal), 0) as total
    FROM pendaftaran p JOIN beasiswa b ON p.beasiswa_id = b.id
    WHERE p.status = 'diterima'
")['total'];

// Statistik per beasiswa
$statsByBeasiswa = fetchAll("
    SELECT b.nama_beasiswa, b.kuota, b.nominal,
    COUNT(p.id) as total,
    SUM(CASE WHEN p.status='diterima' THEN 1 ELSE 0 END) as diterima,
    SUM(CASE WHEN p.status='ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN p.status='menunggu' THEN 1 ELSE 0 END) as menunggu
    FROM beasiswa b
    LEFT JOIN pendaftaran p ON b.id = p.beasiswa_id
    GROUP BY b.id ORDER BY total DESC
");

// Tren pendaftaran 6 bulan
$perBulan = fetchAll("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan,
    DATE_FORMAT(created_at, '%b %Y') as label,
    COUNT(*) as total
    FROM pendaftaran
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY bulan ORDER BY bulan ASC
");
$maxBulan = max(array_column($perBulan, 'total') ?: [1]);

// Registrasi user per bulan
$regPerBulan = fetchAll("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan,
    DATE_FORMAT(created_at, '%b %Y') as label,
    COUNT(*) as total
    FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND role != 'super_admin'
    GROUP BY bulan ORDER BY bulan ASC
");
$maxReg = max(array_column($regPerBulan, 'total') ?: [1]);
// ============================================
// Bug 6 Fix: Export Excel (CSV format)
// ============================================
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $exportData = fetchAll("
        SELECT 
            p.id,
            u.nama as mahasiswa_nama,
            u.email,
            p.nim,
            p.jurusan,
            p.semester,
            p.ipk,
            p.penghasilan_ortu,
            b.nama_beasiswa,
            b.nominal,
            COALESCE(p.deadline_snapshot, b.deadline) as deadline,
            p.status,
            p.catatan_admin,
            p.created_at
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        JOIN beasiswa b ON p.beasiswa_id = b.id
        ORDER BY p.created_at DESC
    ");

    $filename = 'Laporan_Beasiswa_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // BOM untuk Excel agar karakter Indonesia terbaca
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header kolom
    fputcsv($out, [
        'No', 'Nama Mahasiswa', 'Email', 'NIM', 'Jurusan', 'Semester',
        'IPK', 'Penghasilan Ortu (Rp)', 'Nama Beasiswa', 'Dana/Bulan (Rp)',
        'Deadline', 'Status', 'Catatan Admin', 'Tanggal Daftar'
    ]);

    foreach ($exportData as $i => $row) {
        $statusLabel = ['menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];
        fputcsv($out, [
            $i + 1,
            $row['mahasiswa_nama'],
            $row['email'],
            $row['nim'],
            $row['jurusan'],
            $row['semester'],
            number_format($row['ipk'], 2, '.', ''),
            $row['penghasilan_ortu'],
            $row['nama_beasiswa'],
            $row['nominal'],
            $row['deadline'],
            $statusLabel[$row['status']] ?? $row['status'],
            $row['catatan_admin'] ?? '',
            date('d/m/Y H:i', strtotime($row['created_at']))
        ]);
    }

    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem — Super Admin BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/superadmin.css">
</head>
<body class="dashboard-page superadmin-page">
    <aside class="sidebar superadmin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php"><span class="brand-icon">👑</span><span>BeasiswaKu</span></a>
            <span class="sa-badge">SUPER ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group-label">Dashboard</div>
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">🏠</span><span>Overview</span></a>
            <div class="nav-group-label">Manajemen User</div>
            <a href="kelola_admin.php" class="nav-item"><span class="ni-icon">🛡️</span><span>Kelola Admin</span></a>
            <a href="kelola_users.php" class="nav-item"><span class="ni-icon">👥</span><span>Semua Pengguna</span></a>
            <div class="nav-group-label">Sistem</div>
            <a href="kelola_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Kelola Beasiswa</span></a>
            <a href="laporan.php" class="nav-item active"><span class="ni-icon">📊</span><span>Laporan Sistem</span></a>
            <a href="../admin/dashboard.php" class="nav-item"><span class="ni-icon">🔗</span><span>Panel Admin</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout"><span class="ni-icon">🚪</span><span>Keluar</span></a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dash-header sa-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info">
                <h2>Laporan Sistem</h2>
                <p>Ringkasan komprehensif seluruh data sistem beasiswa</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button onclick="window.print()" class="btn-outline">🖨️ Cetak</button>
                <a href="?export=excel" class="btn-sm-primary" style="text-decoration:none;padding:8px 16px;display:inline-flex;align-items:center;gap:6px;">
                    📅 Export Excel
                </a>
            </div>
        </header>

        <!-- KPI Cards -->
        <div class="sa-section-title">📊 KPI Sistem</div>
        <div class="stats-grid sa-stats-grid">
            <div class="stat-card sa-card-gold">
                <div class="sc-icon">💰</div>
                <div class="sc-info">
                    <span class="sc-num" style="font-size:20px"><?= formatRupiah($danaTersalurkan) ?></span>
                    <span class="sc-label">Dana Tersalurkan/Bulan</span>
                </div>
            </div>
            <div class="stat-card stat-blue">
                <div class="sc-icon">👥</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalUsers ?></span>
                    <span class="sc-label">Total User Aktif</span>
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
                <div class="sc-icon">📊</div>
                <div class="sc-info">
                    <span class="sc-num"><?= $totalPendaftar > 0 ? round(($totalDiterima / $totalPendaftar) * 100) : 0 ?>%</span>
                    <span class="sc-label">Tingkat Kelulusan</span>
                </div>
            </div>
        </div>

        <!-- Chart Grid -->
        <div class="chart-grid">
            <!-- Status pendaftaran -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3>Distribusi Status Pendaftaran</h3>
                    <p>Proporsi dari total <?= $totalPendaftar ?> pendaftar</p>
                </div>
                <div class="bar-chart">
                    <?php
                    $items = [
                        ['Diterima', $totalDiterima, 'bar-success'],
                        ['Menunggu', $totalMenunggu, 'bar-warning'],
                        ['Ditolak', $totalDitolak, 'bar-danger'],
                    ];
                    foreach ($items as [$label, $val, $cls]):
                        $pct = $totalPendaftar > 0 ? round(($val / $totalPendaftar) * 100) : 0;
                    ?>
                    <div class="bar-item">
                        <span class="bar-label"><?= $label ?></span>
                        <div class="bar-track" style="position: relative; overflow: visible;">
                            <div class="bar-fill <?= $cls ?>" style="width: <?= $pct ?>%; <?= $val == 0 ? 'display: none;' : '' ?>">
                                <?php if ($pct >= 35): ?>
                                    <?= "$val ($pct%)" ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($pct < 35): ?>
                                <span style="position: absolute; left: calc(<?= $pct ?>% + 8px); top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 700; color: var(--text-muted); white-space: nowrap;">
                                    <?= "$val ($pct%)" ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tren pendaftaran -->
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
                        <div class="bar-track" style="position: relative; overflow: visible;">
                            <div class="bar-fill bar-primary" style="width: <?= $pct ?>%; <?= $b['total'] == 0 ? 'display: none;' : '' ?>">
                                <?php if ($pct >= 15): ?>
                                    <?= $b['total'] ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($pct < 15 && $b['total'] > 0): ?>
                                <span style="position: absolute; left: calc(<?= $pct ?>% + 8px); top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 700; color: var(--text-muted); white-space: nowrap;">
                                    <?= $b['total'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color:var(--text-muted);font-size:14px">Belum ada data.</p>
                <?php endif; ?>
            </div>

            <!-- Registrasi user baru -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3>Registrasi Pengguna Baru (6 Bulan)</h3>
                    <p>Pertumbuhan pengguna per bulan</p>
                </div>
                <?php if (!empty($regPerBulan)): ?>
                <div class="bar-chart">
                    <?php foreach ($regPerBulan as $r):
                        $pct = $maxReg > 0 ? round(($r['total'] / $maxReg) * 100) : 0;
                    ?>
                    <div class="bar-item">
                        <span class="bar-label"><?= $r['label'] ?></span>
                        <div class="bar-track" style="position: relative; overflow: visible;">
                            <div class="bar-fill" style="width: <?= $pct ?>%; background: var(--sa-gold); <?= $r['total'] == 0 ? 'display: none;' : '' ?>">
                                <?php if ($pct >= 15): ?>
                                    <?= $r['total'] ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($pct < 15 && $r['total'] > 0): ?>
                                <span style="position: absolute; left: calc(<?= $pct ?>% + 8px); top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 700; color: var(--text-muted); white-space: nowrap;">
                                    <?= $r['total'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color:var(--text-muted);font-size:14px">Belum ada data.</p>
                <?php endif; ?>
            </div>

            <!-- Distribusi user -->
            <div class="chart-section">
                <div class="chart-header">
                    <h3>Komposisi Pengguna</h3>
                    <p>Distribusi role seluruh user</p>
                </div>
                <div class="bar-chart">
                    <?php
                    $total = $totalAdmin + $totalMahasiswa ?: 1;
                    $userItems = [
                        ['Admin', $totalAdmin, 'bar-primary'],
                        ['Mahasiswa', $totalMahasiswa, 'bar-success'],
                    ];
                    foreach ($userItems as [$label, $val, $cls]):
                        $pct = round(($val / $total) * 100);
                    ?>
                    <div class="bar-item">
                        <span class="bar-label"><?= $label ?></span>
                        <div class="bar-track" style="position: relative; overflow: visible;">
                            <div class="bar-fill <?= $cls ?>" style="width: <?= $pct ?>%; <?= $val == 0 ? 'display: none;' : '' ?>">
                                <?php if ($pct >= 35): ?>
                                    <?= "$val ($pct%)" ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($pct < 35): ?>
                                <span style="position: absolute; left: calc(<?= $pct ?>% + 8px); top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 700; color: var(--text-muted); white-space: nowrap;">
                                    <?= "$val ($pct%)" ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Per Beasiswa -->
        <div class="dash-section">
            <div class="ds-header"><h3>📋 Statistik Per Beasiswa</h3></div>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Beasiswa</th>
                            <th>Dana/Bulan</th>
                            <th>Kuota</th>
                            <th>Pendaftar</th>
                            <th>Diterima</th>
                            <th>Ditolak</th>
                            <th>Menunggu</th>
                            <th>Tingkat Lolos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statsByBeasiswa as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($s['nama_beasiswa']) ?></strong></td>
                            <td><?= formatRupiah($s['nominal']) ?></td>
                            <td><?= $s['kuota'] ?></td>
                            <td><?= $s['total'] ?></td>
                            <td><span class="num-badge num-badge-green"><?= $s['diterima'] ?></span></td>
                            <td><span class="num-badge num-badge-red"><?= $s['ditolak'] ?></span></td>
                            <td><span class="num-badge num-badge-yellow"><?= $s['menunggu'] ?></span></td>
                            <td>
                                <?php $rate = $s['total'] > 0 ? round(($s['diterima'] / $s['total']) * 100) : 0; ?>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width:<?= $rate ?>%"></div>
                                    </div>
                                    <span style="font-size:12px;font-weight:700;color:#34d399;min-width:30px"><?= $rate ?>%</span>
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
