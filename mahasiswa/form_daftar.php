<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'mahasiswa') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$userId = $_SESSION['user_id'];
$beasiswaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cek beasiswa valid
$beasiswa = fetchOne("SELECT * FROM beasiswa WHERE id = $beasiswaId AND status = 'aktif'");
if (!$beasiswa) {
    header('Location: daftar_beasiswa.php');
    exit;
}

// Cek sudah daftar dengan validasi gap 1 tahun:
// - status menunggu/diterima: blok permanen
// - status ditolak tapi di tahun yang SAMA: blok ("sudah mendaftar tahun ini")
// - status ditolak di tahun SEBELUMNYA: boleh daftar ulang
$tahunIni = date('Y');
$sudahDaftarAktif = fetchOne(
    "SELECT id FROM pendaftaran 
     WHERE user_id = $userId AND beasiswa_id = $beasiswaId 
     AND status IN ('menunggu', 'diterima')"
);
$sudahDaftarTahunIni = fetchOne(
    "SELECT id FROM pendaftaran 
     WHERE user_id = $userId AND beasiswa_id = $beasiswaId 
     AND YEAR(created_at) = $tahunIni"
);

if ($sudahDaftarAktif) {
    header('Location: dashboard.php?already=1');
    exit;
}
if ($sudahDaftarTahunIni) {
    header('Location: dashboard.php?already_year=1');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = sanitize($_POST['nim']);
    $jurusan = sanitize($_POST['jurusan']);
    $semester = (int)$_POST['semester'];
    $ipk = (float)str_replace(',', '.', $_POST['ipk']);
    $penghasilan = (int)str_replace(['.', ','], ['', ''], $_POST['penghasilan']);
    $alasan = sanitize($_POST['alasan']);

    // Validasi IPK
    if ($ipk < $beasiswa['nilai_minimum']) {
        $error = "IPK kamu ($ipk) tidak memenuhi syarat minimum (" . $beasiswa['nilai_minimum'] . ").";
    } elseif ($beasiswa['batas_pendapatan'] > 0 && $penghasilan > $beasiswa['batas_pendapatan']) {
        $error = "Penghasilan orang tua melebihi batas maksimal (" . formatRupiah($beasiswa['batas_pendapatan']) . ").";
    } else {
        // Upload files
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $files = ['foto_ktp', 'foto_ktm', 'transkrip', 'surat_tidak_mampu'];
        $uploadedFiles = [];
        $uploadError = false;

        foreach ($files as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array(strtolower($ext), $allowed)) {
                    $error = "Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.";
                    $uploadError = true;
                    break;
                }
                $filename = uniqid($field . '_') . '.' . $ext;
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $filename)) {
                    $uploadedFiles[$field] = $filename;
                } else {
                    $error = "Gagal mengupload file $field.";
                    $uploadError = true;
                    break;
                }
            } else {
                $uploadedFiles[$field] = null;
            }
        }

        if (!$uploadError) {
            $ktp = $uploadedFiles['foto_ktp'] ?? '';
            $ktm = $uploadedFiles['foto_ktm'] ?? '';
            $transkrip = $uploadedFiles['transkrip'] ?? '';
            $surat = $uploadedFiles['surat_tidak_mampu'] ?? '';
            // Bug 4 Fix: Simpan deadline_snapshot saat mendaftar
            $deadlineSnapshot = $beasiswa['deadline'];

            query("INSERT INTO pendaftaran (user_id, beasiswa_id, nim, jurusan, semester, ipk, penghasilan_ortu, alasan, foto_ktp, foto_ktm, transkrip, surat_tidak_mampu, deadline_snapshot)
            VALUES ($userId, $beasiswaId, '$nim', '$jurusan', $semester, $ipk, $penghasilan, '$alasan', '$ktp', '$ktm', '$transkrip', '$surat', '$deadlineSnapshot')");

            // Notifikasi untuk mahasiswa
            $pesanNotif = mysqli_real_escape_string($conn, "Pendaftaran beasiswa " . $beasiswa['nama_beasiswa'] . " berhasil dikirim dan sedang diproses.");
            query("INSERT INTO notifikasi (user_id, pesan) VALUES ($userId, '$pesanNotif')");

            // Bug 5 Fix: Notifikasi ke semua admin dan superadmin
            $namaBeasiswaEsc = mysqli_real_escape_string($conn, $beasiswa['nama_beasiswa']);
            $userNamaEsc = mysqli_real_escape_string($conn, $_SESSION['user_nama']);
            $pesanAdmin = "Pendaftaran baru masuk: $userNamaEsc mendaftar beasiswa $namaBeasiswaEsc. Segera review!";
            $admins = fetchAll("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
            foreach ($admins as $admin) {
                $adminId = $admin['id'];
                query("INSERT INTO notifikasi (user_id, pesan) VALUES ($adminId, '$pesanAdmin')");
            }

            header('Location: dashboard.php?success=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran Beasiswa — BeasiswaKu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/form.css">
</head>
<body class="dashboard-page">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="../index.php">
                <img src="../assets/img/logo.png" alt="Logo" class="brand-logo-img">
                <span>BeasiswaKu</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <span class="ni-icon">🏠</span><span>Dashboard</span>
            </a>
            <a href="daftar_beasiswa.php" class="nav-item active">
                <span class="ni-icon">🎓</span><span>Daftar Beasiswa</span>
            </a>
            <a href="riwayat.php" class="nav-item">
                <span class="ni-icon">📋</span><span>Riwayat Pendaftaran</span>
            </a>
            <a href="profil.php" class="nav-item">
                <span class="ni-icon">👤</span><span>Profil Saya</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item nav-logout">
                <span class="ni-icon">🚪</span><span>Keluar</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="dash-header">
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            <div class="dash-header-info">
                <h2>Form Pendaftaran Beasiswa</h2>
            </div>
            <a href="daftar_beasiswa.php" class="btn-outline">← Kembali</a>
        </header>

        <div class="form-page-container">
            <!-- Info Beasiswa -->
            <div class="beasiswa-info-card">
                <div class="bic-header">
                    <span class="bic-icon">🎓</span>
                    <div>
                        <h3><?= htmlspecialchars($beasiswa['nama_beasiswa']) ?></h3>
                        <p><?= htmlspecialchars($beasiswa['deskripsi']) ?></p>
                    </div>
                </div>
                <div class="bic-meta">
                    <div class="bic-item">
                        <span>💰 Dana:</span>
                        <strong><?= formatRupiah($beasiswa['nominal']) ?>/bulan</strong>
                    </div>
                    <div class="bic-item">
                        <span>📊 IPK Min:</span>
                        <strong><?= number_format($beasiswa['nilai_minimum'], 2) ?></strong>
                    </div>
                    <div class="bic-item">
                        <span>📋 Kuota:</span>
                        <strong><?= $beasiswa['kuota'] ?> orang</strong>
                    </div>
                    <div class="bic-item">
                        <span>📅 Deadline:</span>
                        <strong><?= date('d M Y', strtotime($beasiswa['deadline'])) ?></strong>
                    </div>
                </div>
                <?php if ($beasiswa['syarat']): ?>
                <div class="bic-syarat">
                    <strong>Syarat:</strong>
                    <p><?= nl2br(htmlspecialchars($beasiswa['syarat'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Form -->
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form class="app-form" method="POST" enctype="multipart/form-data" id="formDaftar">
                <div class="form-section">
                    <h4 class="fs-title">📋 Data Akademik</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nim">NIM <span class="req">*</span></label>
                            <input type="text" id="nim" name="nim" placeholder="Nomor Induk Mahasiswa" required>
                        </div>
                        <div class="form-group">
                            <label for="semester">Semester <span class="req">*</span></label>
                            <select id="semester" name="semester" required>
                                <option value="">Pilih Semester</option>
                                <?php for ($i = 1; $i <= 14; $i++): ?>
                                <option value="<?= $i ?>">Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jurusan">Jurusan/Program Studi <span class="req">*</span></label>
                            <input type="text" id="jurusan" name="jurusan" placeholder="Contoh: Teknik Informatika" required>
                        </div>
                        <div class="form-group">
                            <label for="ipk">IPK <span class="req">*</span></label>
                            <input type="text" id="ipk" name="ipk" placeholder="Contoh: 3.75" maxlength="4"
                            pattern="[0-9]+([.,][0-9]{1,2})?" required>
                            <small>IPK minimum: <?= number_format($beasiswa['nilai_minimum'], 2) ?></small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="fs-title">👨‍👩‍👦 Data Ekonomi</h4>
                    <div class="form-group">
                        <label for="penghasilan">Penghasilan Orang Tua per Bulan <span class="req">*</span></label>
                        <div class="input-prefix">
                            <span>Rp</span>
                            <input type="text" id="penghasilan" name="penghasilan" placeholder="0" required
                            oninput="formatCurrency(this)">
                        </div>
                        <?php if ($beasiswa['batas_pendapatan'] > 0): ?>
                        <small>Maksimal: <?= formatRupiah($beasiswa['batas_pendapatan']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="fs-title">✍️ Esai Motivasi</h4>
                    <div class="form-group">
                        <label for="alasan">Alasan Mendaftar Beasiswa <span class="req">*</span></label>
                        <textarea id="alasan" name="alasan" rows="5" 
                        placeholder="Tuliskan alasan kamu mendaftar beasiswa ini, motivasi, dan rencanamu ke depan..." 
                        minlength="100" required></textarea>
                        <small><span id="charCount">0</span>/1000 karakter (minimal 100)</small>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="fs-title">📎 Upload Dokumen</h4>
                    <p class="fs-note">Format yang diterima: PDF, JPG, PNG (maks. 5MB per file)</p>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="foto_ktp">Foto KTP</label>
                            <div class="file-upload" id="ktp-upload">
                                <input type="file" id="foto_ktp" name="foto_ktp" accept=".pdf,.jpg,.jpeg,.png" 
                                onchange="updateFileName(this, 'ktp-upload')">
                                <div class="fu-content">
                                    <span class="fu-icon">📄</span>
                                    <span class="fu-text">Klik atau drag file KTP</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="foto_ktm">Foto KTM (Kartu Mahasiswa)</label>
                            <div class="file-upload" id="ktm-upload">
                                <input type="file" id="foto_ktm" name="foto_ktm" accept=".pdf,.jpg,.jpeg,.png"
                                onchange="updateFileName(this, 'ktm-upload')">
                                <div class="fu-content">
                                    <span class="fu-icon">🪪</span>
                                    <span class="fu-text">Klik atau drag file KTM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="transkrip">Transkrip Nilai</label>
                            <div class="file-upload" id="transkrip-upload">
                                <input type="file" id="transkrip" name="transkrip" accept=".pdf,.jpg,.jpeg,.png"
                                onchange="updateFileName(this, 'transkrip-upload')">
                                <div class="fu-content">
                                    <span class="fu-icon">📊</span>
                                    <span class="fu-text">Klik atau drag Transkrip</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="surat_tidak_mampu">Surat Keterangan Tidak Mampu</label>
                            <div class="file-upload" id="sktm-upload">
                                <input type="file" id="surat_tidak_mampu" name="surat_tidak_mampu" accept=".pdf,.jpg,.jpeg,.png"
                                    onchange="updateFileName(this, 'sktm-upload')">
                                <div class="fu-content">
                                    <span class="fu-icon">📜</span>
                                    <span class="fu-text">Klik atau drag SKTM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="daftar_beasiswa.php" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary btn-submit" id="submitBtn">
                        <span class="btn-text">Kirim Pendaftaran</span>
                        <span class="btn-loading hidden">⏳ Mengirim...</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/form.js"></script>
</body>
</html>
