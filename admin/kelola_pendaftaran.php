<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

// Handle actions via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    $catatan = sanitize($_POST['catatan'] ?? '');

    if ($action === 'update_status' && in_array($status, ['diterima', 'ditolak', 'menunggu'])) {
        query("UPDATE pendaftaran SET status = '$status', catatan_admin = '$catatan' WHERE id = $id");

        // Ambil data pendaftaran untuk notifikasi
        $pend = fetchOne("SELECT p.user_id, b.nama_beasiswa FROM pendaftaran p JOIN beasiswa b ON p.beasiswa_id = b.id WHERE p.id = $id");
        if ($pend) {
            $namaBeasiswa = mysqli_real_escape_string($conn, $pend['nama_beasiswa']);
            $pesanMap = [
                'diterima' => "Selamat! Pendaftaran beasiswamu untuk $namaBeasiswa telah DITERIMA.",
                'ditolak'  => "Pendaftaran beasiswamu untuk $namaBeasiswa tidak lolos seleksi.",
                'menunggu' => "Status pendaftaran beasiswamu untuk $namaBeasiswa sedang ditinjau ulang.",
            ];
            $pesan = $pesanMap[$status];
            query("INSERT INTO notifikasi (user_id, pesan) VALUES ({$pend['user_id']}, '$pesan')");
        }

        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    exit;
}

$statusFilter = $_GET['status'] ?? 'semua';
$searchQuery = sanitize($_GET['q'] ?? '');
$beasiswaFilter = (int)($_GET['beasiswa'] ?? 0);

$where = [];
if ($statusFilter !== 'semua') $where[] = "p.status = '$statusFilter'";
if ($searchQuery) $where[] = "(u.nama LIKE '%$searchQuery%' OR u.email LIKE '%$searchQuery%' OR p.nim LIKE '%$searchQuery%')";
if ($beasiswaFilter > 0) $where[] = "p.beasiswa_id = $beasiswaFilter";
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$pendaftaran = fetchAll("
    SELECT p.*, u.nama as mahasiswa_nama, u.email, b.nama_beasiswa, b.nominal
    FROM pendaftaran p
    JOIN users u ON p.user_id = u.id
    JOIN beasiswa b ON p.beasiswa_id = b.id
    $whereClause
    ORDER BY p.created_at DESC
");

$daftarBeasiswa = fetchAll("SELECT id, nama_beasiswa FROM beasiswa ORDER BY nama_beasiswa ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pendaftaran — BeasiswaKu Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="dashboard-page">
    <aside class="sidebar admin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php"><span class="brand-icon">🎓</span><span>BeasiswaKu</span></a>
            <span class="admin-badge">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">📊</span><span>Dashboard</span></a>
            <a href="kelola_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Kelola Beasiswa</span></a>
            <a href="kelola_pendaftaran.php" class="nav-item active"><span class="ni-icon">📋</span><span>Kelola Pendaftaran</span></a>
            <a href="kelola_mahasiswa.php" class="nav-item"><span class="ni-icon">👥</span><span>Data Mahasiswa</span></a>
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
                <h2>Kelola Pendaftaran</h2>
                <p>Total: <?= count($pendaftaran) ?> pendaftaran</p>
            </div>
        </header>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" 
                           placeholder="🔍 Cari nama, NIM, email..." class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="semua" <?= $statusFilter === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="menunggu" <?= $statusFilter === 'menunggu' ? 'selected' : '' ?>>⏳ Menunggu</option>
                        <option value="diterima" <?= $statusFilter === 'diterima' ? 'selected' : '' ?>>✅ Diterima</option>
                        <option value="ditolak" <?= $statusFilter === 'ditolak' ? 'selected' : '' ?>>❌ Ditolak</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="beasiswa" class="filter-select">
                        <option value="0">Semua Beasiswa</option>
                        <?php foreach ($daftarBeasiswa as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $beasiswaFilter === (int)$b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nama_beasiswa']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-sm-primary">Filter</button>
                <a href="kelola_pendaftaran.php" class="btn-sm">Reset</a>
            </form>
        </div>

        <!-- Tabel -->
        <div class="dash-section">
            <?php if (empty($pendaftaran)): ?>
            <div class="empty-dash">
                <div class="ed-icon">📭</div>
                <p>Tidak ada pendaftaran ditemukan.</p>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mahasiswa</th>
                            <th>NIM</th>
                            <th>Beasiswa</th>
                            <th>IPK</th>
                            <th>Penghasilan Ortu</th>
                            <th>Tgl Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendaftaran as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($p['mahasiswa_nama']) ?></strong>
                                <small><?= htmlspecialchars($p['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($p['nim']) ?></td>
                            <td><?= htmlspecialchars($p['nama_beasiswa']) ?></td>
                            <td><?= number_format($p['ipk'], 2) ?></td>
                            <td><?= formatRupiah($p['penghasilan_ortu']) ?></td>
                            <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $p['status'] ?>">
                                    <?php
                                    $labels = ['menunggu' => '⏳ Menunggu', 'diterima' => '✅ Diterima', 'ditolak' => '❌ Ditolak'];
                                    echo $labels[$p['status']] ?? $p['status'];
                                    ?>
                                </span>
                            </td>
                            <td class="td-actions">
                                <a href="detail_pendaftaran.php?id=<?= $p['id'] ?>" class="btn-sm">Detail</a>
                                <?php if ($p['status'] === 'menunggu'): ?>
                                <button onclick="openModal(<?= $p['id'] ?>, 'diterima', '<?= htmlspecialchars($p['mahasiswa_nama']) ?>')" class="btn-sm btn-green">Terima</button>
                                <button onclick="openModal(<?= $p['id'] ?>, 'ditolak', '<?= htmlspecialchars($p['mahasiswa_nama']) ?>')" class="btn-sm btn-red">Tolak</button>
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

    <!-- Modal Konfirmasi -->
    <div class="modal-overlay hidden" id="modalOverlay" onclick="closeModal()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <h3 id="modalTitle">Konfirmasi Keputusan</h3>
            <p id="modalDesc"></p>
            <div class="form-group">
                <label for="catatanAdmin">Catatan / Alasan (opsional)</label>
                <textarea id="catatanAdmin" rows="3" placeholder="Tulis catatan untuk mahasiswa..."></textarea>
            </div>
            <div class="modal-actions">
                <button onclick="closeModal()" class="btn-secondary">Batal</button>
                <button onclick="submitDecision()" class="btn-primary" id="btnConfirm">Konfirmasi</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
