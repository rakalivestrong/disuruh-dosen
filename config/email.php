<?php
// ============================================
// KONFIGURASI EMAIL (Gmail SMTP)
// ============================================

// ⚠️ ISI SESUAI AKUN GMAIL KAMU:
define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',   'emailkamu@gmail.com');    // ← ganti dengan Gmail kamu
define('MAIL_PASSWORD',   'xxxx xxxx xxxx xxxx');    // ← App Password Gmail (bukan password biasa!)
define('MAIL_FROM_EMAIL', 'emailkamu@gmail.com');    // ← sama dengan username
define('MAIL_FROM_NAME',  'BeasiswaKu - Sistem Informasi Beasiswa');

// ============================================
// CARA MENDAPATKAN APP PASSWORD GMAIL:
// 1. Buka: https://myaccount.google.com/security
// 2. Aktifkan "2-Step Verification" dulu
// 3. Cari "App passwords" → buat baru → pilih "Mail" & "Windows Computer"
// 4. Copy 16 karakter yang muncul → paste ke MAIL_PASSWORD di atas
// ============================================
?>
