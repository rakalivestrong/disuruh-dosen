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
    deadline_snapshot DATE NULL COMMENT 'Snapshot deadline saat mendaftar, tidak berubah meski admin edit beasiswa',
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

-- Contoh data beasiswa (referensi nyata Indonesia)
INSERT INTO beasiswa (nama_beasiswa, deskripsi, syarat, kuota, nilai_minimum, batas_pendapatan, deadline, status, nominal) VALUES
-- Beasiswa contoh dasar
('Beasiswa Berprestasi', 'Beasiswa untuk mahasiswa dengan prestasi akademik tinggi', 'IPK minimal 3.5, Aktif berorganisasi, Surat rekomendasi dosen', 50, 3.5, 5000000, '2026-08-31', 'aktif', 1500000),
('Beasiswa Kurang Mampu', 'Beasiswa untuk mahasiswa dari keluarga kurang mampu', 'Penghasilan orang tua < Rp 3.000.000, Surat keterangan tidak mampu, IPK minimal 2.75', 100, 2.75, 3000000, '2026-09-15', 'aktif', 1000000),
('Beasiswa Peningkatan Prestasi Akademik', 'Beasiswa PPA dari pemerintah untuk mahasiswa aktif', 'Semester minimal 2, IPK minimal 3.0, Tidak sedang menerima beasiswa lain', 75, 3.0, 6000000, '2026-07-30', 'aktif', 750000),
-- Referensi beasiswa nyata Indonesia
('KIP-K (Kartu Indonesia Pintar Kuliah)', 'Beasiswa pemerintah Indonesia dari Kemendikbud untuk mahasiswa dari keluarga kurang mampu secara ekonomi namun memiliki potensi akademik yang baik. KIP-K menanggung seluruh biaya pendidikan dan memberikan biaya hidup bulanan.', '1. Mahasiswa aktif semester 1-8 (D4/S1) atau 1-6 (D3)
2. Tidak sedang menerima beasiswa lain dari pemerintah
3. Penghasilan kotor gabungan orang tua/wali paling banyak Rp 4.000.000/bulan atau per kapita Rp 750.000/bulan
4. Prioritas pemegang KIP SMA/SMK
5. Melampirkan: KTP, KTM, KK, Surat Keterangan Tidak Mampu dari kelurahan, transkrip nilai', 500, 0.00, 4000000, '2026-09-30', 'aktif', 950000),
('Beasiswa PPA (Peningkatan Prestasi Akademik) Kemendikbud', 'Beasiswa dari Kemendikbud yang diberikan kepada mahasiswa aktif berprestasi akademik tinggi. Diberikan selama 12 bulan dan dapat diperpanjang.', '1. Mahasiswa aktif minimal semester 2
2. IPK minimal 3.00 (skala 4.00)
3. Tidak sedang menerima beasiswa lain dari pemerintah
4. Aktif dalam kegiatan ekstrakurikuler / organisasi
5. Dokumen: KTM, transkrip nilai resmi, surat keterangan aktif kuliah, surat rekomendasi dosen/dekan', 200, 3.00, 6000000, '2026-08-15', 'aktif', 800000),
('Beasiswa BBP-PPA (Bantuan Biaya Pendidikan)', 'Beasiswa dari Kemendikbud untuk mahasiswa berprestasi dari keluarga kurang mampu. Kombinasi antara prestasi akademik dan kebutuhan ekonomi.', '1. Mahasiswa aktif minimal semester 2
2. IPK minimal 2.75 (skala 4.00)
3. Penghasilan orang tua/wali maksimal Rp 3.000.000/bulan
4. Tidak sedang menerima beasiswa lain dari pemerintah
5. Dokumen: KTP, KTM, transkrip nilai, Surat Keterangan Tidak Mampu, slip gaji/surat penghasilan orang tua', 150, 2.75, 3000000, '2026-08-31', 'aktif', 700000),
('Beasiswa Bank Indonesia (BI)', 'Program beasiswa dari Bank Indonesia untuk mahasiswa berprestasi dengan kepedulian sosial tinggi. Penerima bergabung dalam komunitas Generasi Baru Indonesia (GenBI).', '1. Mahasiswa aktif semester 4 hingga semester 7
2. IPK minimal 3.00 (skala 4.00)
3. Aktif dalam kegiatan organisasi kemahasiswaan
4. Tidak sedang menerima beasiswa lain
5. Penghasilan orang tua maksimal Rp 5.000.000/bulan
6. Dokumen: KTP, KTM, transkrip nilai, sertifikat organisasi, esai motivasi, surat rekomendasi', 100, 3.00, 5000000, '2026-07-31', 'aktif', 1000000),
('Beasiswa Djarum Plus', 'Program beasiswa dari Djarum Foundation untuk mahasiswa berprestasi tinggi yang juga aktif dalam kegiatan kemahasiswaan. Penerima mendapat program pembinaan dan pengembangan karakter.', '1. Mahasiswa aktif semester 4, 5, atau 6
2. IPK minimal 3.20 (skala 4.00)
3. Aktif berorganisasi di dalam maupun luar kampus
4. Tidak sedang menerima beasiswa dari lembaga lain
5. Lulus seleksi administrasi, tes tertulis, dan wawancara
6. Dokumen: KTP, KTM, transkrip nilai, sertifikat organisasi, pas foto, esai motivasi', 75, 3.20, 8000000, '2026-07-15', 'aktif', 1000000),
('Beasiswa Tanoto Foundation (TELADAN)', 'Beasiswa dari Tanoto Foundation melalui program TELADAN — mewujudkan potensi, memimpin perubahan. Fokus pada pengembangan pemimpin masa depan Indonesia.', '1. Mahasiswa aktif semester 2 atau 4
2. IPK minimal 3.00 (skala 4.00)
3. Aktif di organisasi kemahasiswaan
4. Penghasilan orang tua tidak lebih dari Rp 6.000.000/bulan
5. Bersedia mengikuti program pengembangan Tanoto Foundation
6. Dokumen: KTP, KTM, transkrip nilai, surat keterangan penghasilan orang tua, essay kepemimpinan, surat rekomendasi dosen', 80, 3.00, 6000000, '2026-08-20', 'aktif', 1200000),
('Beasiswa Yayasan Supersemar', 'Salah satu program beasiswa tertua di Indonesia dari Yayasan Supersemar. Diberikan kepada mahasiswa yang memiliki potensi akademik namun terkendala biaya, prioritas mahasiswa daerah terpencil.', '1. Mahasiswa aktif minimal semester 3
2. IPK minimal 2.50 (skala 4.00)
3. Penghasilan orang tua/wali maksimal Rp 2.500.000/bulan
4. Berkelakuan baik (tidak pernah terlibat kasus narkoba/kriminal)
5. Dokumen: KTP, KTM, transkrip nilai, Surat Keterangan Tidak Mampu, surat keterangan berkelakuan baik', 200, 2.50, 2500000, '2026-09-15', 'aktif', 600000),
('Beasiswa Yayasan Pendidikan Telkom', 'Program beasiswa dari Yayasan Pendidikan Telkom (YPT) untuk mahasiswa berprestasi di bidang teknologi dan informatika. Diutamakan untuk program studi teknik dan sains.', '1. Mahasiswa aktif program studi Teknik/Sains/TI semester 3-7
2. IPK minimal 3.00 (skala 4.00)
3. Penghasilan orang tua maksimal Rp 6.000.000/bulan
4. Memiliki minat di bidang teknologi dan inovasi
5. Tidak sedang menerima beasiswa penuh dari institusi lain
6. Dokumen: KTP, KTM, transkrip nilai, surat keterangan aktif kuliah, esai motivasi bidang teknologi', 60, 3.00, 6000000, '2026-08-10', 'aktif', 900000);
