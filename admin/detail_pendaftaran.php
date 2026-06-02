<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$isAdmin = in_array($_SESSION['user_role'], ['admin', 'super_admin']);
$userId = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

$whereUser = $isAdmin ? '' : "AND p.user_id = $userId";
$pendaftaran = fetchOne("
    SELECT p.*, u.nama as mahasiswa_nama, u.email, 
    b.nama_beasiswa, b.nominal, b.deadline, b.deskripsi as b_desc
    FROM pendaftaran p
    JOIN users u ON p.user_id = u.id
    JOIN beasiswa b ON p.beasiswa_id = b.id
    WHERE p.id = $id $whereUser
");

if (!$pendaftaran) {
    header($isAdmin ? 'Location: kelola_pendaftaran.php' : 'Location: dashboard.php');
    exit;
}

$backUrl = $isAdmin ? 'kelola_pendaftaran.php' : 'dashboard.php';
$basePath = $isAdmin ? '../' : '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftaran — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/admin.css">
</head>
<body class="dashboard-page">
    <aside class="sidebar <?= $isAdmin ? 'admin-sidebar' : '' ?>" id="sidebar">
        <div class="sidebar-brand">
            <a href="<?= $basePath ?>index.php">
                <img src="<?= $basePath ?>assets/img/logo.png" alt="Logo" class="brand-logo-img"><span>BeasiswaKu</span>
            </a>
            <?php if ($isAdmin): ?><span class="admin-badge">ADMIN</span><?php endif; ?>
        </div>
        <nav class="sidebar-nav">
            <?php if ($isAdmin): ?>
            <a href="<?= $basePath ?>admin/dashboard.php" class="nav-item"><span class="ni-icon">📊</span><span>Dashboard</span></a>
            <a href="<?= $basePath ?>admin/kelola_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Kelola Beasiswa</span></a>
            <a href="<?= $basePath ?>admin/kelola_pendaftaran.php" class="nav-item active"><span class="ni-icon">📋</span><span>Kelola Pendaftaran</span></a>
            <a href="<?= $basePath ?>admin/kelola_mahasiswa.php" class="nav-item"><span class="ni-icon">👥</span><span>Data Mahasiswa</span></a>
            <?php else: ?>
            <a href="<?= $basePath ?>mahasiswa/dashboard.php" class="nav-item active"><span class="ni-icon">🏠</span><span>Dashboard</span></a>
            <a href="<?= $basePath ?>mahasiswa/daftar_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Daftar Beasiswa</span></a>
            <a href="<?= $basePath ?>mahasiswa/riwayat.php" class="nav-item"><span class="ni-icon">📋</span><span>Riwayat</span></a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= $basePath ?>auth/logout.php" class="nav-item nav-logout"><span class="ni-icon">🚪</span><span>Keluar</span></a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info">
                <h2>Detail Pendaftaran</h2>
                <p><?= htmlspecialchars($pendaftaran['nama_beasiswa']) ?></p>
            </div>
            <a href="<?= $backUrl ?>" class="btn-outline">← Kembali</a>
        </header>

        <!-- Status Banner -->
        <div class="dash-section" style="background: <?php
            $colors = ['menunggu'=>'rgba(245,158,11,0.1)', 'diterima'=>'rgba(16,185,129,0.1)', 'ditolak'=>'rgba(239,68,68,0.1)'];
            echo $colors[$pendaftaran['status']] ?? 'rgba(255,255,255,0.05)';
        ?>; border-color: <?php
            $bcolors = ['menunggu'=>'rgba(245,158,11,0.3)', 'diterima'=>'rgba(16,185,129,0.3)', 'ditolak'=>'rgba(239,68,68,0.3)'];
            echo $bcolors[$pendaftaran['status']] ?? 'var(--border)';
        ?>; padding: 20px 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div>
                    <div style="font-size:13px; color:var(--text-muted); margin-bottom:4px">Status Pendaftaran</div>
                    <span class="status-badge status-<?= $pendaftaran['status'] ?>" style="font-size:15px; padding: 6px 16px;">
                        <?php
                        $labels = ['menunggu'=>'⏳ Menunggu Review', 'diterima'=>'✅ Diterima', 'ditolak'=>'❌ Tidak Lolos'];
                        echo $labels[$pendaftaran['status']];
                        ?>
                    </span>
                </div>
                <div style="text-align:right">
                    <div style="font-size:12px; color:var(--text-muted)">Tanggal Mendaftar</div>
                    <div style="font-weight:700"><?= date('d F Y, H:i', strtotime($pendaftaran['created_at'])) ?></div>
                </div>
            </div>
            <?php if ($pendaftaran['catatan_admin']): ?>
            <div style="margin-top:14px; padding:12px; background:rgba(0,0,0,0.2); border-radius:8px;">
                <strong style="font-size:12px; color:var(--text-muted)">Catatan Admin:</strong>
                <p style="margin-top:4px; font-size:14px;"><?= nl2br(htmlspecialchars($pendaftaran['catatan_admin'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="detail-grid">
            <!-- Data Mahasiswa -->
            <div class="detail-card">
                <h4>👤 Data Mahasiswa</h4>
                <?php if ($isAdmin): ?>
                <div class="detail-row">
                    <span class="detail-label">Nama</span>
                    <span class="detail-val"><?= htmlspecialchars($pendaftaran['mahasiswa_nama']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-val"><?= htmlspecialchars($pendaftaran['email']) ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">NIM</span>
                    <span class="detail-val"><?= htmlspecialchars($pendaftaran['nim']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Jurusan</span>
                    <span class="detail-val"><?= htmlspecialchars($pendaftaran['jurusan']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Semester</span>
                    <span class="detail-val"><?= $pendaftaran['semester'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">IPK</span>
                    <span class="detail-val"><?= number_format($pendaftaran['ipk'], 2) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Penghasilan Ortu</span>
                    <span class="detail-val"><?= formatRupiah($pendaftaran['penghasilan_ortu']) ?>/bln</span>
                </div>
            </div>

            <!-- Info Beasiswa -->
            <div class="detail-card">
                <h4>🎓 Info Beasiswa</h4>
                <div class="detail-row">
                    <span class="detail-label">Nama Beasiswa</span>
                    <span class="detail-val"><?= htmlspecialchars($pendaftaran['nama_beasiswa']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Dana/Bulan</span>
                    <span class="detail-val" style="color:var(--primary-light)"><?= formatRupiah($pendaftaran['nominal']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Deadline</span>
                    <span class="detail-val"><?= date('d M Y', strtotime($pendaftaran['deadline'])) ?></span>
                </div>
            </div>

            <!-- Esai Motivasi -->
            <div class="detail-card" style="grid-column: 1/-1;">
                <h4>✍️ Esai Motivasi</h4>
                <div style="background:rgba(255,255,255,0.04); border-radius:8px; padding:16px; line-height:1.7; font-size:14px; color:var(--text-muted);">
                    <?= nl2br(htmlspecialchars($pendaftaran['alasan'])) ?>
                </div>
            </div>

            <!-- Dokumen -->
            <div class="detail-card" style="grid-column: 1/-1;">
                <h4>📎 Dokumen</h4>
                <div class="dokumen-grid">
                    <?php
                    $docs = [
                        'foto_ktp' => ['KTP', '🪪'],
                        'foto_ktm' => ['KTM', '🎓'],
                        'transkrip' => ['Transkrip Nilai', '📊'],
                        'surat_tidak_mampu' => ['Surat Keterangan', '📜'],
                    ];
                    foreach ($docs as $key => [$label, $icon]):
                    ?>
                    <div class="doc-item">
                        <span class="doc-icon"><?= $icon ?></span>
                        <div>
                            <div style="font-size:12px; color:var(--text-muted); margin-bottom:2px"><?= $label ?></div>
                            <?php if ($pendaftaran[$key]): ?>
                            <a href="<?= $basePath ?>uploads/<?= htmlspecialchars($pendaftaran[$key]) ?>" target="_blank"
                               style="font-size:13px; color:var(--primary-light); font-weight:600">
                                Lihat File →
                            </a>
                            <?php else: ?>
                            <span style="font-size:13px; color:var(--text-dim)">Tidak diupload</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Admin Decision Panel -->
            <?php if ($isAdmin && $pendaftaran['status'] === 'menunggu'): ?>
            <div class="detail-card decision-panel" style="grid-column: 1/-1;">
                <h4>⚖️ Keputusan Admin</h4>
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:16px;">
                    Tinjau data di atas dan tentukan keputusan untuk pendaftaran ini.
                </p>
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:600; color:var(--text-muted);">Catatan (opsional)</label>
                    <textarea id="catatanDecision" rows="3" placeholder="Tuliskan catatan atau alasan keputusan..."
                              style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.06); border:1px solid var(--border-light); border-radius:12px; color:var(--text); font-family:inherit; font-size:14px; margin-top:6px; resize:vertical;"></textarea>
                </div>
                <div class="decision-buttons">
                    <button class="btn-accept" onclick="directDecision(<?= $id ?>, 'diterima')">✅ Terima Pendaftaran</button>
                    <button class="btn-reject" onclick="directDecision(<?= $id ?>, 'ditolak')">❌ Tolak Pendaftaran</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="<?= $basePath ?>assets/js/dashboard.js"></script>
    <script>
    async function directDecision(id, status) {
        const catatan = document.getElementById('catatanDecision')?.value || '';
        if (!confirm(`Yakin ingin ${status === 'diterima' ? 'MENERIMA' : 'MENOLAK'} pendaftaran ini?`)) return;

        const fd = new FormData();
        fd.append('ajax','1'); fd.append('action','update_status');
        fd.append('id', id); fd.append('status', status); fd.append('catatan', catatan);

        const resp = await fetch('<?= $basePath ?>admin/kelola_pendaftaran.php', { method:'POST', body:fd });
        const result = await resp.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Gagal memproses keputusan.');
        }
    }
    </script>
</body>
</html>
