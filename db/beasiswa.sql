-- ============================================
-- DATABASE SISTEM BEASISWA
-- ============================================

CREATE DATABASE IF NOT EXISTS db_beasiswa;
USE db_beasiswa;

-- Tabel Users (Super Admin, Admin & Mahasiswa)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'mahasiswa') DEFAULT 'mahasiswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Beasiswa
CREATE TABLE IF NOT EXISTS beasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_beasiswa VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    syarat TEXT,
    kuota INT DEFAULT 0,
    nilai_minimum FLOAT DEFAULT 0,
    batas_pendapatan BIGINT DEFAULT 0,
    deadline DATE,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    nominal BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pendaftaran Beasiswa
CREATE TABLE IF NOT EXISTS pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    beasiswa_id INT NOT NULL,
    nim VARCHAR(20) NOT NULL,
    jurusan VARCHAR(100),
    semester INT,
    ipk FLOAT,
    penghasilan_ortu BIGINT,
    alasan TEXT,
    foto_ktp VARCHAR(255),
    foto_ktm VARCHAR(255),
    transkrip VARCHAR(255),
    surat_tidak_mampu VARCHAR(255),
    status ENUM('menunggu', 'diterima', 'ditolak') DEFAULT 'menunggu',
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (beasiswa_id) REFERENCES beasiswa(id) ON DELETE CASCADE
);

-- Tabel Notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pesan TEXT NOT NULL,
    dibaca TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- DATA AWAL (SEED)
-- ============================================

-- Super Admin default
-- Gunakan reset_superadmin.php untuk generate hash yang benar
-- atau jalankan: php -r "echo password_hash('superadmin123', PASSWORD_BCRYPT);"
-- Lalu ganti hash di bawah ini
INSERT INTO users (nama, email, password, role) VALUES
('Super Administrator', 'superadmin@beasiswa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- Admin default (password: admin123)
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@beasiswa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Contoh data beasiswa
INSERT INTO beasiswa (nama_beasiswa, deskripsi, syarat, kuota, nilai_minimum, batas_pendapatan, deadline, status, nominal) VALUES
('Beasiswa Berprestasi', 'Beasiswa untuk mahasiswa dengan prestasi akademik tinggi', 'IPK minimal 3.5, Aktif berorganisasi, Surat rekomendasi dosen', 50, 3.5, 5000000, '2026-08-31', 'aktif', 1500000),
('Beasiswa Kurang Mampu', 'Beasiswa untuk mahasiswa dari keluarga kurang mampu', 'Penghasilan orang tua < Rp 3.000.000, Surat keterangan tidak mampu, IPK minimal 2.75', 100, 2.75, 3000000, '2026-09-15', 'aktif', 1000000),
('Beasiswa Peningkatan Prestasi Akademik', 'Beasiswa PPA dari pemerintah untuk mahasiswa aktif', 'Semester minimal 2, IPK minimal 3.0, Tidak sedang menerima beasiswa lain', 75, 3.0, 6000000, '2026-07-30', 'aktif', 750000);
