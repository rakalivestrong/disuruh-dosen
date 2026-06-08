<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

// Tambah atau Edit Beasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = sanitize($_POST['nama_beasiswa']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $syarat = sanitize($_POST['syarat']);
    $kuota = (int)$_POST['kuota'];
    $nilai_min = (float)str_replace(',', '.', $_POST['nilai_minimum']);
    $batas_pendapatan = (int)str_replace(['.', ','], ['', ''], $_POST['batas_pendapatan']);
    $deadline = sanitize($_POST['deadline']);
    $status = sanitize($_POST['status']);
    $nominal = (int)str_replace(['.', ','], ['', ''], $_POST['nominal']);

    if ($id > 0) {
        query("UPDATE beasiswa SET nama_beasiswa='$nama', deskripsi='$deskripsi', syarat='$syarat', 
        kuota=$kuota, nilai_minimum=$nilai_min, batas_pendapatan=$batas_pendapatan, 
        deadline='$deadline', status='$status', nominal=$nominal WHERE id=$id");
        $message = 'Beasiswa berhasil diperbarui!';
    } else {
        query("INSERT INTO beasiswa (nama_beasiswa, deskripsi, syarat, kuota, nilai_minimum, batas_pendapatan, deadline, status, nominal)
        VALUES ('$nama', '$deskripsi', '$syarat', $kuota, $nilai_min, $batas_pendapatan, '$deadline', '$status', $nominal)");
        $message = 'Beasiswa baru berhasil ditambahkan!';
    }
}

// Hapus beasiswa
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    query("DELETE FROM beasiswa WHERE id = $delId");
    header('Location: kelola_beasiswa.php?deleted=1');
    exit;
}

// Toggle status
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $cur = fetchOne("SELECT status FROM beasiswa WHERE id = $tid");
    if ($cur) {
        $newStatus = $cur['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        query("UPDATE beasiswa SET status='$newStatus' WHERE id=$tid");
        header('Location: kelola_beasiswa.php');
        exit;
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editData = fetchOne("SELECT * FROM beasiswa WHERE id = $editId");
}

$beasiswaList = fetchAll("
    SELECT b.*, COUNT(p.id) as total_pendaftar,
    SUM(CASE WHEN p.status='diterima' THEN 1 ELSE 0 END) as total_diterima
    FROM beasiswa b
    LEFT JOIN pendaftaran p ON b.id = p.beasiswa_id
    GROUP BY b.id
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Beasiswa — Super Admin BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/form.css">
    <link rel="stylesheet" href="../assets/css/superadmin.css">
</head>
<body class="dashboard-page superadmin-page">
    <aside class="sidebar superadmin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php"><img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img"><span>BeasiswaKu</span></a>
            <span class="sa-badge">SUPER ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group-label">Dashboard</div>
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">🏠</span><span>Overview</span></a>
            <div class="nav-group-label">Manajemen User</div>
            <a href="kelola_admin.php" class="nav-item"><span class="ni-icon">🛡️</span><span>Kelola Admin</span></a>
            <a href="kelola_users.php" class="nav-item"><span class="ni-icon">👥</span><span>Semua Pengguna</span></a>
            <div class="nav-group-label">Sistem</div>
            <a href="kelola_beasiswa.php" class="nav-item active"><span class="ni-icon">🎓</span><span>Kelola Beasiswa</span></a>
            <a href="laporan.php" class="nav-item"><span class="ni-icon">📊</span><span>Laporan Sistem</span></a>
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
                <h2>Kelola Beasiswa</h2>
                <p>Buat dan kelola program beasiswa sistem</p>
            </div>
            <button class="header-action-btn" onclick="toggleForm()">+ Tambah Beasiswa</button>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-error">Beasiswa berhasil dihapus.</div>
        <?php endif; ?>

        <!-- Form -->
        <div class="dash-section" id="formSection" style="<?= ($editData || isset($_POST['nama_beasiswa'])) ? '' : 'display:none' ?>">
            <div class="ds-header">
                <h3><?= $editData ? '✏️ Edit Beasiswa' : '➕ Tambah Beasiswa Baru' ?></h3>
                <button onclick="toggleForm()" class="btn-sm">✕ Tutup</button>
            </div>
            <form class="app-form" method="POST">
                <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group form-full">
                        <label>Nama Beasiswa <span class="req">*</span></label>
                        <input type="text" name="nama_beasiswa" value="<?= htmlspecialchars($editData['nama_beasiswa'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-full">
                        <label>Deskripsi <span class="req">*</span></label>
                        <textarea name="deskripsi" rows="3" required><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-full">
                        <label>Syarat</label>
                        <textarea name="syarat" rows="3"><?= htmlspecialchars($editData['syarat'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kuota (orang) <span class="req">*</span></label>
                        <input type="number" name="kuota" value="<?= $editData['kuota'] ?? 0 ?>" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>IPK Minimum <span class="req">*</span></label>
                        <input type="text" name="nilai_minimum" value="<?= $editData['nilai_minimum'] ?? '0.00' ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Batas Penghasilan Ortu (Rp)</label>
                        <input type="text" name="batas_pendapatan" value="<?= $editData['batas_pendapatan'] ?? 0 ?>" oninput="formatCurrency(this)">
                    </div>
                    <div class="form-group">
                        <label>Dana Beasiswa/Bulan (Rp) <span class="req">*</span></label>
                        <input type="text" name="nominal" value="<?= $editData['nominal'] ?? 0 ?>" oninput="formatCurrency(this)" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Deadline <span class="req">*</span></label>
                        <input type="date" name="deadline" value="<?= $editData['deadline'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="aktif" <?= ($editData['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= ($editData['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary sa-btn-gold">
                        <?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Beasiswa' ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Beasiswa -->
        <div class="dash-section">
            <div class="ds-header"><h3>🎓 Daftar Program Beasiswa (<?= count($beasiswaList) ?>)</h3></div>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Beasiswa</th>
                            <th>Dana/Bulan</th>
                            <th>Kuota</th>
                            <th>IPK Min</th>
                            <th>Pendaftar</th>
                            <th>Diterima</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($beasiswaList as $i => $b): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($b['nama_beasiswa']) ?></strong></td>
                            <td><?= formatRupiah($b['nominal']) ?></td>
                            <td><?= $b['kuota'] ?> orang</td>
                            <td><?= number_format($b['nilai_minimum'], 2) ?></td>
                            <td><span class="num-badge num-badge-yellow"><?= $b['total_pendaftar'] ?></span></td>
                            <td><span class="num-badge num-badge-green"><?= $b['total_diterima'] ?></span></td>
                            <td><?= date('d M Y', strtotime($b['deadline'])) ?></td>
                            <td>
                                <a href="kelola_beasiswa.php?toggle=<?= $b['id'] ?>"
                                class="status-badge <?= $b['status'] === 'aktif' ? 'status-diterima' : 'status-ditolak' ?>"
                                onclick="return confirm('Ubah status beasiswa ini?')" style="cursor:pointer;text-decoration:none">
                                    <?= $b['status'] === 'aktif' ? '🟢 Aktif' : '🔴 Nonaktif' ?>
                                </a>
                            </td>
                            <td class="td-actions">
                                <a href="kelola_beasiswa.php?edit=<?= $b['id'] ?>" class="btn-edit">Edit</a>
                                <a href="kelola_beasiswa.php?delete=<?= $b['id'] ?>"
                                class="btn-delete"
                                onclick="return confirm('Hapus beasiswa ini? Semua pendaftaran terkait akan ikut terhapus!')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/form.js"></script>
    <script>
    function toggleForm() {
        const s = document.getElementById('formSection');
        s.style.display = s.style.display === 'none' ? '' : 'none';
    }
    </script>
</body>
</html>
