<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

// Hapus admin
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $target = fetchOne("SELECT * FROM users WHERE id = $delId AND role = 'admin'");
    if ($target) {
        query("DELETE FROM users WHERE id = $delId");
        header('Location: kelola_admin.php?deleted=1');
        exit;
    } else {
        $error = 'Admin tidak ditemukan atau tidak valid.';
    }
}

// Tambah / Edit admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id'] ?? 0);
    $nama  = sanitize($_POST['nama']);
    $email = sanitize($_POST['email']);
    $pass  = $_POST['password'] ?? '';

    if ($id > 0) {
        // Edit
        $existing = fetchOne("SELECT id FROM users WHERE email = '$email' AND id != $id");
        if ($existing) {
            $error = 'Email sudah digunakan oleh user lain.';
        } else {
            if (!empty($pass)) {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                query("UPDATE users SET nama='$nama', email='$email', password='$hashed' WHERE id=$id AND role='admin'");
            } else {
                query("UPDATE users SET nama='$nama', email='$email' WHERE id=$id AND role='admin'");
            }
            $message = 'Data admin berhasil diperbarui!';
        }
    } else {
        // Tambah
        if (empty($pass) || strlen($pass) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            $existing = fetchOne("SELECT id FROM users WHERE email = '$email'");
            if ($existing) {
                $error = 'Email sudah terdaftar!';
            } else {
                $hashed = password_hash($pass, PASSWORD_DEFAULT);
                query("INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$hashed', 'admin')");
                $message = 'Admin baru berhasil ditambahkan!';
            }
        }
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editData = fetchOne("SELECT * FROM users WHERE id = $editId AND role = 'admin'");
}

$showForm = isset($_GET['action']) && $_GET['action'] === 'tambah' || $editData || $error;

$adminList = fetchAll("
    SELECT u.*, 
           COUNT(DISTINCT p.id) as total_pendaftaran_diproses
    FROM users u
    LEFT JOIN pendaftaran p ON 1=1
    WHERE u.role = 'admin'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin — Super Admin BeasiswaKu</title>
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
            <a href="kelola_admin.php" class="nav-item active"><span class="ni-icon">🛡️</span><span>Kelola Admin</span></a>
            <a href="kelola_users.php" class="nav-item"><span class="ni-icon">👥</span><span>Semua Pengguna</span></a>
            <div class="nav-group-label">Sistem</div>
            <a href="kelola_beasiswa.php" class="nav-item"><span class="ni-icon">🎓</span><span>Kelola Beasiswa</span></a>
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
                <h2>Kelola Admin</h2>
                <p>Buat dan kelola akun administrator sistem</p>
            </div>
            <a href="kelola_admin.php?action=tambah" class="header-action-btn">+ Tambah Admin</a>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-error">Admin berhasil dihapus dari sistem.</div>
        <?php endif; ?>

        <!-- Form Tambah/Edit Admin -->
        <div class="dash-section" id="formSection" style="<?= $showForm ? '' : 'display:none' ?>">
            <div class="ds-header">
                <h3><?= $editData ? '✏️ Edit Admin' : '➕ Tambah Admin Baru' ?></h3>
                <button onclick="toggleForm()" class="btn-sm">✕ Tutup</button>
            </div>
            <form class="app-form" method="POST">
                <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="req">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars($editData['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password <?= $editData ? '(kosongkan jika tidak diubah)' : '<span class="req">*</span>' ?></label>
                        <input type="password" name="password" placeholder="Min. 6 karakter" <?= $editData ? '' : 'required' ?>>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary sa-btn-gold">
                        <?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Admin' ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Admin -->
        <div class="dash-section">
            <div class="ds-header">
                <h3>🛡️ Daftar Administrator (<?= count($adminList) ?>)</h3>
            </div>
            <?php if (empty($adminList)): ?>
            <div class="empty-dash"><div class="ed-icon">🛡️</div><p>Belum ada admin terdaftar.</p></div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Admin</th>
                            <th>Email</th>
                            <th>Tgl Dibuat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adminList as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="mini-avatar admin-av"><?= strtoupper(substr($a['nama'], 0, 1)) ?></div>
                                    <div>
                                        <strong><?= htmlspecialchars($a['nama']) ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                            <td><span class="sa-role-badge admin">🛡️ Admin</span></td>
                            <td class="td-actions">
                                <a href="kelola_admin.php?edit=<?= $a['id'] ?>" class="btn-edit">Edit</a>
                                <a href="kelola_admin.php?delete=<?= $a['id'] ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Hapus admin <?= htmlspecialchars($a['nama']) ?>? Tindakan ini tidak dapat dibatalkan.')">Hapus</a>
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
    <script>
    function toggleForm() {
        const s = document.getElementById('formSection');
        s.style.display = s.style.display === 'none' ? '' : 'none';
    }
    </script>
</body>
</html>
