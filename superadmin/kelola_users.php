<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$message = '';
$error   = '';

// Reset password user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $targetId = (int)($_POST['user_id'] ?? 0);
    $action   = $_POST['action'];

    if ($action === 'reset_password') {
        $newPass = $_POST['new_password'] ?? '';
        if (strlen($newPass) < 6) {
            $error = 'Password minimal 6 karakter!';
        } else {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            query("UPDATE users SET password='$hashed' WHERE id=$targetId AND role != 'super_admin'");
            $message = 'Password berhasil direset!';
        }
    } elseif ($action === 'ubah_role') {
        $newRole = sanitize($_POST['new_role'] ?? '');
        if (in_array($newRole, ['admin', 'mahasiswa'])) {
            query("UPDATE users SET role='$newRole' WHERE id=$targetId AND role != 'super_admin'");
            $message = 'Role user berhasil diubah!';
        }
    } elseif ($action === 'hapus_user') {
        $target = fetchOne("SELECT * FROM users WHERE id = $targetId AND role != 'super_admin'");
        if ($target) {
            query("DELETE FROM users WHERE id = $targetId");
            $message = 'User berhasil dihapus dari sistem.';
        } else {
            $error = 'User tidak ditemukan atau tidak dapat dihapus.';
        }
    }
}

// Filter
$roleFilter = $_GET['role'] ?? 'semua';
$searchQ    = sanitize($_GET['q'] ?? '');

$where = ["u.role != 'super_admin'"];
if ($roleFilter !== 'semua') $where[] = "u.role = '$roleFilter'";
if ($searchQ) $where[] = "(u.nama LIKE '%$searchQ%' OR u.email LIKE '%$searchQ%')";
$whereClause = 'WHERE ' . implode(' AND ', $where);

$userList = fetchAll("
    SELECT u.*,
    COUNT(p.id) as total_daftar
    FROM users u
    LEFT JOIN pendaftaran p ON u.id = p.user_id
    $whereClause
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna — Super Admin BeasiswaKu</title>
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
            <a href="kelola_users.php" class="nav-item active"><span class="ni-icon">👥</span><span>Semua Pengguna</span></a>
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
                <h2>Semua Pengguna</h2>
                <p>Total: <?= count($userList) ?> pengguna ditemukan</p>
            </div>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>"
                        placeholder="🔍 Cari nama atau email..." class="filter-input">
                </div>
                <div class="filter-group">
                    <select name="role" class="filter-select">
                        <option value="semua" <?= $roleFilter === 'semua' ? 'selected' : '' ?>>Semua Role</option>
                        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>🛡️ Admin</option>
                        <option value="mahasiswa" <?= $roleFilter === 'mahasiswa' ? 'selected' : '' ?>>🎓 Mahasiswa</option>
                    </select>
                </div>
                <button type="submit" class="btn-sm-primary">Filter</button>
                <a href="kelola_users.php" class="btn-sm">Reset</a>
            </form>
        </div>

        <!-- Tabel -->
        <div class="dash-section">
            <?php if (empty($userList)): ?>
            <div class="empty-dash"><div class="ed-icon">👥</div><p>Tidak ada pengguna ditemukan.</p></div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Pengajuan</th>
                            <th>Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userList as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="mini-avatar <?= $u['role'] === 'admin' ? 'admin-av' : '' ?>">
                                        <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                                    </div>
                                    <strong><?= htmlspecialchars($u['nama']) ?></strong>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php
                                $roleMap = [
                                    'admin'     => ['🛡️ Admin', 'sa-role-badge admin'],
                                    'mahasiswa' => ['🎓 Mahasiswa', 'sa-role-badge mhs'],
                                ];
                                [$rl, $rc] = $roleMap[$u['role']] ?? [$u['role'], 'sa-role-badge'];
                                echo "<span class=\"$rc\">$rl</span>";
                                ?>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'mahasiswa'): ?>
                                <span class="status-badge status-menunggu"><?= $u['total_daftar'] ?> pengajuan</span>
                                <?php else: ?>
                                <span style="color:var(--text-dim);font-size:13px">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td class="td-actions">
                                <button onclick="openResetModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>')" class="btn-sm">Reset Pass</button>
                                <button onclick="openRoleModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>', '<?= $u['role'] ?>')" class="btn-sm sa-btn-outline">Ubah Role</button>
                                <button onclick="hapusUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama']) ?>')" class="btn-sm btn-red">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Reset Password -->
    <div class="modal-overlay hidden" id="modalReset" onclick="closeModals()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <h3>🔑 Reset Password</h3>
            <p id="resetDesc" style="color:var(--text-muted);font-size:14px;margin-bottom:16px"></p>
            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="resetUserId">
                <div class="form-group">
                    <label>Password Baru <span class="req">*</span></label>
                    <input type="password" name="new_password" placeholder="Min. 6 karakter" required>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModals()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ubah Role -->
    <div class="modal-overlay hidden" id="modalRole" onclick="closeModals()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <h3>🔄 Ubah Role Pengguna</h3>
            <p id="roleDesc" style="color:var(--text-muted);font-size:14px;margin-bottom:16px"></p>
            <form method="POST">
                <input type="hidden" name="action" value="ubah_role">
                <input type="hidden" name="user_id" id="roleUserId">
                <div class="form-group">
                    <label>Role Baru</label>
                    <select name="new_role" id="newRoleSelect">
                        <option value="admin">🛡️ Admin</option>
                        <option value="mahasiswa">🎓 Mahasiswa</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeModals()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary sa-btn-gold">Simpan Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form hapus tersembunyi -->
    <form id="formHapus" method="POST" style="display:none">
        <input type="hidden" name="action" value="hapus_user">
        <input type="hidden" name="user_id" id="hapusUserId">
    </form>

    <script src="../assets/js/dashboard.js"></script>
    <script>
    function openResetModal(id, nama) {
        document.getElementById('resetUserId').value = id;
        document.getElementById('resetDesc').textContent = 'Reset password untuk: ' + nama;
        document.getElementById('modalReset').classList.remove('hidden');
    }
    function openRoleModal(id, nama, currentRole) {
        document.getElementById('roleUserId').value = id;
        document.getElementById('roleDesc').textContent = 'Mengubah role untuk: ' + nama + ' (saat ini: ' + currentRole + ')';
        document.getElementById('newRoleSelect').value = currentRole;
        document.getElementById('modalRole').classList.remove('hidden');
    }
    function hapusUser(id, nama) {
        if (!confirm('Hapus pengguna "' + nama + '"? Semua data terkait akan ikut terhapus!')) return;
        document.getElementById('hapusUserId').value = id;
        document.getElementById('formHapus').submit();
    }
    function closeModals() {
        document.querySelectorAll('.modal-overlay').forEach(m => m.classList.add('hidden'));
    }
    </script>
</body>
</html>
