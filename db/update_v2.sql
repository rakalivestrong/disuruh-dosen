-- ============================================
-- DATABASE UPDATE v2 - Bug Fixes & Referensi Beasiswa
-- Jalankan script ini di phpMyAdmin atau MySQL CLI
-- ============================================

USE db_beasiswa;

-- ============================================
-- FIX BUG 4: Tambah kolom deadline_snapshot
-- Menyimpan deadline saat mahasiswa mendaftar
-- agar tidak berubah ketika admin edit beasiswa
-- ============================================
ALTER TABLE pendaftaran 
ADD COLUMN deadline_snapshot DATE NULL AFTER beasiswa_id;

-- Update data yang sudah ada: isi dengan deadline beasiswa saat ini
UPDATE pendaftaran p
JOIN beasiswa b ON p.beasiswa_id = b.id
SET p.deadline_snapshot = b.deadline
WHERE p.deadline_snapshot IS NULL;

-- ============================================
-- FIX BUG 5: Notifikasi untuk admin
-- Tabel notifikasi sudah ada, tinggal kirim ke admin
-- user_id = NULL berarti untuk semua admin
-- ============================================

-- ============================================
-- DATA REFERENSI BEASISWA NYATA INDONESIA
-- ============================================

-- Hapus data contoh lama jika ada (opsional, hapus comment jika ingin reset)
-- DELETE FROM beasiswa WHERE nama_beasiswa IN ('Beasiswa Berprestasi', 'Beasiswa Kurang Mampu', 'Beasiswa Peningkatan Prestasi Akademik');

-- 1. KIP-K (Kartu Indonesia Pintar Kuliah)
INSERT INTO beasiswa (nama_beasiswa, deskripsi, syarat, kuota, nilai_minimum, batas_pendapatan, deadline, status, nominal) VALUES
('KIP-K (Kartu Indonesia Pintar Kuliah)',
'Beasiswa pemerintah Indonesia dari Kemendikbud untuk mahasiswa dari keluarga kurang mampu secara ekonomi namun memiliki potensi akademik yang baik. KIP-K menanggung seluruh biaya pendidikan dan memberikan biaya hidup bulanan.',
'1. Mahasiswa aktif semester 1-8 (D4/S1) atau 1-6 (D3)\n2. Tidak sedang menerima beasiswa lain dari pemerintah\n3. Penghasilan kotor gabungan orang tua/wali paling banyak Rp 4.000.000/bulan atau per kapita Rp 750.000/bulan\n4. Prioritas pemegang KIP SMA/SMK\n5. Melampirkan: KTP, KTM, KK, Surat Keterangan Tidak Mampu dari kelurahan, transkrip nilai',
500, 0.00, 4000000, '2026-09-30', 'aktif', 950000),

-- 2. PPA (Peningkatan Prestasi Akademik)
('Beasiswa PPA (Peningkatan Prestasi Akademik)',
'Beasiswa dari Kemendikbud yang diberikan kepada mahasiswa aktif berprestasi akademik tinggi. Beasiswa ini diberikan selama 12 bulan dan dapat diperpanjang berdasarkan prestasi akademik.',
'1. Mahasiswa aktif minimal semester 2\n2. IPK minimal 3.00 (skala 4.00)\n3. Tidak sedang menerima beasiswa lain dari pemerintah\n4. Aktif dalam kegiatan ekstrakurikuler / organisasi\n5. Dokumen: KTM, transkrip nilai resmi, surat keterangan aktif kuliah, surat rekomendasi dosen/dekan',
200, 3.00, 6000000, '2026-08-15', 'aktif', 800000),

-- 3. BBP-PPA (Bantuan Biaya Pendidikan - PPA)
('Beasiswa BBP-PPA (Bantuan Biaya Pendidikan)',
'Beasiswa dari Kemendikbud yang diperuntukkan bagi mahasiswa berprestasi yang berasal dari keluarga kurang mampu. Merupakan kombinasi antara prestasi akademik dan kebutuhan ekonomi.',
'1. Mahasiswa aktif minimal semester 2\n2. IPK minimal 2.75 (skala 4.00)\n3. Penghasilan orang tua/wali maksimal Rp 3.000.000/bulan\n4. Tidak sedang menerima beasiswa lain dari pemerintah\n5. Dokumen: KTP, KTM, transkrip nilai, Surat Keterangan Tidak Mampu, slip gaji/surat penghasilan orang tua',
150, 2.75, 3000000, '2026-08-31', 'aktif', 700000),

-- 4. Beasiswa Bank Indonesia (BI)
('Beasiswa Bank Indonesia (BI)',
'Program beasiswa dari Bank Indonesia yang diberikan kepada mahasiswa berprestasi dengan kepedulian sosial tinggi. Penerima bergabung dalam komunitas Generasi Baru Indonesia (GenBI) dan mendapat program pengembangan diri.',
'1. Mahasiswa aktif semester 4 hingga semester 7\n2. IPK minimal 3.00 (skala 4.00)\n3. Aktif dalam kegiatan organisasi kemahasiswaan\n4. Tidak sedang menerima beasiswa lain\n5. Penghasilan orang tua maksimal Rp 5.000.000/bulan\n6. Dokumen: KTP, KTM, transkrip nilai, sertifikat organisasi, esai motivasi, surat rekomendasi',
100, 3.00, 5000000, '2026-07-31', 'aktif', 1000000),

-- 5. Beasiswa Djarum Plus
('Beasiswa Djarum Plus',
'Program beasiswa dari Djarum Foundation untuk mahasiswa berprestasi tinggi yang juga aktif dalam kegiatan kemahasiswaan. Selain dana pendidikan, penerima mendapat program pembinaan dan pengembangan karakter.',
'1. Mahasiswa aktif semester 4, 5, atau 6\n2. IPK minimal 3.20 (skala 4.00)\n3. Aktif berorganisasi di dalam maupun luar kampus\n4. Tidak sedang menerima beasiswa dari lembaga lain\n5. Lulus seleksi administrasi, tes tertulis, dan wawancara\n6. Dokumen: KTP, KTM, transkrip nilai, sertifikat organisasi, pas foto, esai motivasi',
75, 3.20, 8000000, '2026-07-15', 'aktif', 1000000),

-- 6. Beasiswa Tanoto Foundation
('Beasiswa Tanoto Foundation (TELADAN)',
'Beasiswa dari Tanoto Foundation melalui program TELADAN (mewujudkan potensi, memimpin perubahan). Fokus pada pengembangan pemimpin masa depan Indonesia dengan pendekatan holistik akademik dan kepemimpinan.',
'1. Mahasiswa aktif semester 2 atau 4\n2. IPK minimal 3.00 (skala 4.00)\n3. Aktif di organisasi kemahasiswaan\n4. Penghasilan orang tua tidak lebih dari Rp 6.000.000/bulan\n5. Bersedia mengikuti program pengembangan Tanoto Foundation\n6. Dokumen: KTP, KTM, transkrip nilai, surat keterangan penghasilan orang tua, essay kepemimpinan, surat rekomendasi dosen',
80, 3.00, 6000000, '2026-08-20', 'aktif', 1200000),

-- 7. Beasiswa Yayasan Supersemar
('Beasiswa Yayasan Supersemar',
'Salah satu program beasiswa tertua di Indonesia dari Yayasan Supersemar. Diberikan kepada mahasiswa yang memiliki potensi akademik namun terkendala biaya, dengan prioritas mahasiswa dari daerah terpencil.',
'1. Mahasiswa aktif minimal semester 3\n2. IPK minimal 2.50 (skala 4.00)\n3. Penghasilan orang tua/wali maksimal Rp 2.500.000/bulan\n4. Berkelakuan baik (tidak pernah terlibat kasus narkoba/kriminal)\n5. Dokumen: KTP, KTM, transkrip nilai, Surat Keterangan Tidak Mampu, surat keterangan berkelakuan baik',
200, 2.50, 2500000, '2026-09-15', 'aktif', 600000),

-- 8. Beasiswa Yayasan Pendidikan Telkom
('Beasiswa Yayasan Pendidikan Telkom',
'Program beasiswa dari Yayasan Pendidikan Telkom (YPT) untuk mahasiswa berprestasi di bidang teknologi dan informatika. Diutamakan untuk program studi teknik dan sains.',
'1. Mahasiswa aktif program studi Teknik/Sains/TI semester 3-7\n2. IPK minimal 3.00 (skala 4.00)\n3. Penghasilan orang tua maksimal Rp 6.000.000/bulan\n4. Memiliki minat di bidang teknologi dan inovasi\n5. Tidak sedang menerima beasiswa penuh dari institusi lain\n6. Dokumen: KTP, KTM, transkrip nilai, surat keterangan aktif kuliah, esai motivasi bidang teknologi',
60, 3.00, 6000000, '2026-08-10', 'aktif', 900000);

SELECT 'Database update v2 berhasil!' AS status;
