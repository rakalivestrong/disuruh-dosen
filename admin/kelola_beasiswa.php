<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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

// Edit: ambil data
$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editData = fetchOne("SELECT * FROM beasiswa WHERE id = $editId");
}

$beasiswaList = fetchAll("SELECT * FROM beasiswa ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Beasiswa — BeasiswaKu Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/form.css">
</head>
<body class="dashboard-page">
    <aside class="sidebar admin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php"><span class="brand-icon">🎓</span><span>BeasiswaKu</span></a>
            <span class="admin-badge">ADMIN</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><span class="ni-icon">📊</span><span>Dashboard</span></a>
            <a href="kelola_beasiswa.php" class="nav-item active"><span class="ni-icon">🎓</span><span>Kelola Beasiswa</span></a>
            <a href="kelola_pendaftaran.php" class="nav-item"><span class="ni-icon">📋</span><span>Kelola Pendaftaran</span></a>
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
                <h2>Kelola Beasiswa</h2>
            </div>
            <button class="btn-primary" onclick="toggleForm()">➕ Tambah Beasiswa</button>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-error">Beasiswa berhasil dihapus.</div>
        <?php endif; ?>

        <!-- Form Tambah/Edit -->
        <div class="dash-section" id="formSection" style="<?= ($editData || isset($_POST['nama_beasiswa'])) ? '' : 'display:none' ?>">
            <div class="ds-header">
                <h3><?= $editData ? 'Edit Beasiswa' : 'Tambah Beasiswa Baru' ?></h3>
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
                    <button type="submit" class="btn-primary">
                        <?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Beasiswa' ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Beasiswa -->
        <div class="dash-section">
            <div class="ds-header"><h3>Daftar Beasiswa</h3></div>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Beasiswa</th>
                            <th>Dana/Bulan</th>
                            <th>Kuota</th>
                            <th>IPK Min</th>
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
                            <td><?= date('d M Y', strtotime($b['deadline'])) ?></td>
                            <td>
                                <span class="status-badge <?= $b['status'] === 'aktif' ? 'status-diterima' : 'status-ditolak' ?>">
                                    <?= $b['status'] === 'aktif' ? '🟢 Aktif' : '🔴 Nonaktif' ?>
                                </span>
                            </td>
                            <td class="td-actions">
                                <a href="kelola_beasiswa.php?edit=<?= $b['id'] ?>" class="btn-sm">Edit</a>
                                <a href="kelola_beasiswa.php?delete=<?= $b['id'] ?>" 
                                   class="btn-sm btn-red"
                                   onclick="return confirm('Yakin hapus beasiswa ini?')">Hapus</a>
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
        const section = document.getElementById('formSection');
        section.style.display = section.style.display === 'none' ? '' : 'none';
    }
    </script>
</body>
</html>
