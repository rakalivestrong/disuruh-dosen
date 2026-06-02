<?php
session_start();
require_once __DIR__ . '/config/db.php';

$beasiswaList = fetchAll("SELECT * FROM beasiswa WHERE status = 'aktif' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeasiswaKu — Sistem Informasi Beasiswa Mahasiswa</title>
    <meta name="description" content="Platform pendaftaran beasiswa mahasiswa online. Temukan dan daftar beasiswa terbaik sesuai kualifikasi Anda.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/beranda.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="assets/img/logo.png" alt="Logo" class="brand-logo-img">
                <span>BeasiswaKu</span>
            </a>
            <div class="nav-links">
                <a href="#beasiswa" class="nav-link">Beasiswa</a>
                <a href="#cara-daftar" class="nav-link">Cara Daftar</a>
                <a href="#tentang" class="nav-link">Tentang</a>
            </div>
            <div class="nav-auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'super_admin'): ?>
                        <a href="superadmin/dashboard.php" class="btn-outline">Dashboard Super Admin</a>
                    <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn-outline">Dashboard Admin</a>
                    <?php else: ?>
                        <a href="mahasiswa/dashboard.php" class="btn-outline">Dashboard Saya</a>
                    <?php endif; ?>
                    <a href="auth/logout.php" class="btn-secondary">Keluar</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn-outline">Masuk</a>
                    <a href="auth/login.php" class="btn-primary">Daftar Sekarang</a>
                <?php endif; ?>
            </div>
            <button class="hamburger" id="hamburger" onclick="toggleMenu()">
                <span></span><span></span><span></span>
            </button>
        </div>
        <!-- Mobile menu -->
        <div class="mobile-menu" id="mobileMenu">
            <a href="#beasiswa">Beasiswa</a>
            <a href="#cara-daftar">Cara Daftar</a>
            <a href="#tentang">Tentang</a>
            <a href="auth/login.php" class="btn-primary">Masuk / Daftar</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg">
            <div class="hero-orb orb1"></div>
            <div class="hero-orb orb2"></div>
            <div class="hero-orb orb3"></div>
            <div class="hero-grid"></div>
        </div>
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                Pendaftaran Beasiswa 2026 Dibuka
            </div>
            <h1 class="hero-title">
                Raih <span class="gradient-text">Mimpimu</span><br>dengan Beasiswa Terbaik
            </h1>
            <p class="hero-subtitle">
                Platform pendaftaran beasiswa mahasiswa yang mudah, cepat, dan transparan. 
                Temukan beasiswa yang sesuai dan wujudkan cita-citamu bersama kami.
            </p>
            <div class="hero-actions">
                <a href="auth/login.php" class="btn-hero-primary">
                    Daftar Beasiswa
                    <span class="btn-arrow-right">→</span>
                </a>
                <a href="#beasiswa" class="btn-hero-secondary">
                    Lihat Beasiswa
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-num"><?= count($beasiswaList) ?>+</span>
                    <span class="stat-label">Jenis Beasiswa</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">225+</span>
                    <span class="stat-label">Kuota Tersedia</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-num">100%</span>
                    <span class="stat-label">Transparan</span>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="card-float card-f1">
                <span class="cf-icon">🏆</span>
                <div>
                    <strong>Beasiswa Berprestasi</strong>
                    <p>Rp 1.500.000/bln</p>
                </div>
            </div>
            <div class="card-float card-f2">
                <span class="cf-icon">✅</span>
                <div>
                    <strong>Diterima!</strong>
                    <p>Selamat, kamu lolos seleksi</p>
                </div>
            </div>
            <div class="card-float card-f3">
                <span class="cf-icon">📊</span>
                <div>
                    <strong>IPK 3.85</strong>
                    <p>Memenuhi syarat</p>
                </div>
            </div>
            <div class="hero-phone">
                <div class="phone-screen">
                    <div class="ps-header">
                        <img src="assets/img/logo.png" alt="Logo" class="brand-logo-img" style="height: 18px; margin-right: 4px;">
                        <span>BeasiswaKu</span>
                        <span class="ps-notif">3</span>
                    </div>
                    <div class="ps-card">
                        <p class="ps-label">Status Pendaftaran</p>
                        <div class="ps-status accepted">✓ Diterima</div>
                        <p class="ps-name">Beasiswa Berprestasi 2026</p>
                    </div>
                    <div class="ps-card ps-card2">
                        <p class="ps-label">Dana Beasiswa</p>
                        <p class="ps-amount">Rp 1.500.000</p>
                        <p class="ps-period">per bulan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Daftar Beasiswa -->
    <section class="section" id="beasiswa">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Program Aktif</span>
                <h2 class="section-title">Beasiswa Tersedia</h2>
                <p class="section-subtitle">Pilih beasiswa yang sesuai dengan kriteria dan kebutuhanmu</p>
            </div>
            <div class="beasiswa-grid">
                <?php if (empty($beasiswaList)): ?>
                <div class="empty-state">
                    <p>Belum ada beasiswa aktif saat ini.</p>
                </div>
                <?php else: ?>
                <?php foreach ($beasiswaList as $b): ?>
                <div class="beasiswa-card" data-aos="fade-up">
                    <div class="bc-header">
                        <div class="bc-icon">🎓</div>
                        <span class="bc-badge">Aktif</span>
                    </div>
                    <h3 class="bc-title"><?= htmlspecialchars($b['nama_beasiswa']) ?></h3>
                    <p class="bc-desc"><?= htmlspecialchars(substr($b['deskripsi'], 0, 120)) ?>...</p>
                    <div class="bc-info">
                        <div class="bc-info-item">
                            <span class="bci-label">💰 Dana</span>
                            <span class="bci-val"><?= formatRupiah($b['nominal']) ?>/bln</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="bci-label">📋 Kuota</span>
                            <span class="bci-val"><?= $b['kuota'] ?> Orang</span>
                        </div>
                        <div class="bc-info-item">
                            <span class="bci-label">📊 IPK Min.</span>
                            <span class="bci-val"><?= number_format($b['nilai_minimum'], 2) ?></span>
                        </div>
                        <div class="bc-info-item">
                            <span class="bci-label">📅 Deadline</span>
                            <span class="bci-val"><?= date('d M Y', strtotime($b['deadline'])) ?></span>
                        </div>
                    </div>
                    <a href="auth/login.php" class="btn-card">Daftar Sekarang →</a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Cara Daftar -->
    <section class="section section-dark" id="cara-daftar">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Mudah & Cepat</span>
                <h2 class="section-title">Cara Mendaftar</h2>
                <p class="section-subtitle">Hanya 4 langkah untuk mendaftar beasiswa impianmu</p>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-num">01</div>
                    <div class="step-icon">📝</div>
                    <h3>Buat Akun</h3>
                    <p>Daftarkan diri dengan email aktif dan buat password yang kuat</p>
                </div>
                <div class="step-arrow">→</div>
                <div class="step-card">
                    <div class="step-num">02</div>
                    <div class="step-icon">🔍</div>
                    <h3>Pilih Beasiswa</h3>
                    <p>Jelajahi beasiswa yang tersedia dan pilih yang sesuai kriteria</p>
                </div>
                <div class="step-arrow">→</div>
                <div class="step-card">
                    <div class="step-num">03</div>
                    <div class="step-icon">📤</div>
                    <h3>Upload Dokumen</h3>
                    <p>Lengkapi data diri dan unggah dokumen yang diperlukan</p>
                </div>
                <div class="step-arrow">→</div>
                <div class="step-card">
                    <div class="step-num">04</div>
                    <div class="step-icon">🏆</div>
                    <h3>Pantau Status</h3>
                    <p>Cek status pendaftaran secara real-time di dashboard kamu</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang -->
    <section class="section" id="tentang">
        <div class="container">
            <div class="about-grid">
                <div class="about-content">
                    <span class="section-badge">Tentang Kami</span>
                    <h2>Platform Beasiswa <span class="gradient-text">Terpercaya</span></h2>
                    <p>BeasiswaKu adalah sistem informasi beasiswa digital yang dirancang untuk memudahkan mahasiswa dalam mencari dan mendaftar beasiswa. Kami berkomitmen untuk mewujudkan pendidikan yang lebih merata dan berkualitas.</p>
                    <ul class="about-list">
                        <li>✅ Proses pendaftaran 100% online</li>
                        <li>✅ Status seleksi real-time & transparan</li>
                        <li>✅ Notifikasi langsung ke akun kamu</li>
                        <li>✅ Dukungan berbagai jenis beasiswa</li>
                    </ul>
                    <a href="auth/login.php" class="btn-primary">Mulai Sekarang →</a>
                </div>
                <div class="about-visual">
                    <div class="av-card av-c1">
                        <span>📈</span>
                        <div>
                            <strong>Tingkat Kelulusan</strong>
                            <p>87% pendaftar lolos seleksi</p>
                        </div>
                    </div>
                    <div class="av-card av-c2">
                        <span>👨‍🎓</span>
                        <div>
                            <strong>500+ Alumni</strong>
                            <p>Mahasiswa penerima beasiswa</p>
                        </div>
                    </div>
                    <div class="av-card av-c3">
                        <span>⭐</span>
                        <div>
                            <strong>Rating 4.9/5</strong>
                            <p>Kepuasan pengguna</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <a href="index.php" class="nav-brand">
                        <img src="assets/img/logo.png" alt="Logo" class="brand-logo-img">
                        <span>BeasiswaKu</span>
                    </a>
                    <p>Platform pendaftaran beasiswa mahasiswa yang mudah dan transparan.</p>
                </div>
                <div class="footer-links">
                    <h4>Navigasi</h4>
                    <ul>
                        <li><a href="#beasiswa">Beasiswa</a></li>
                        <li><a href="#cara-daftar">Cara Daftar</a></li>
                        <li><a href="#tentang">Tentang</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Akun</h4>
                    <ul>
                        <li><a href="auth/login.php">Masuk</a></li>
                        <li><a href="auth/login.php">Daftar</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 BeasiswaKu. Dibuat dengan ❤️ untuk mahasiswa Indonesia.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
