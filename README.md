# 🎓 BeasiswaKu — Panduan Setup & Instalasi

## Persyaratan

- **XAMPP** (Apache + PHP + MySQL) atau **Laragon**
- PHP versi 7.4+
- MySQL / MariaDB

---

## Langkah Instalasi

### 1. Pindahkan Project

Salin seluruh folder `project` ke direktori web server:

- **XAMPP**: `C:\xampp\htdocs\project`
- **Laragon**: `C:\laragon\www\project`

---

### 2. Setup Database

1. Buka **phpMyAdmin** di browser: `http://localhost/phpmyadmin`
2. Buat database baru bernama `db_beasiswa`
3. Import file SQL: `db/beasiswa.sql`
4. Atau jalankan query di tab SQL phpMyAdmin

---

### 3. Konfigurasi Database

Edit file `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // username MySQL kamu
define('DB_PASS', '');           // password MySQL kamu (kosong di XAMPP default)
define('DB_NAME', 'db_beasiswa');
```

---

### 4. Jalankan Aplikasi

Buka browser dan akses:

```
http://localhost/project/
```

---

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| **Super Admin** | superadmin@beasiswa.com | superadmin123 |
| **Admin** | admin@beasiswa.com | password |

> **Catatan:** Password di database disimpan sebagai hash.
> 
> ⚠️ Gunakan akun demo di atas untuk login pertama kali sesuai role.

---

## Struktur Folder

```
project/
├── 📁 superadmin/          # Halaman superadmin
│   ├── dashboard.php       # Dashboard utama superadmin
│   ├── kelola_admin.php    # CRUD admin
│   ├── kelola_users.php    # Kelola semua user (mahasiswa & admin)
│   ├── kelola_beasiswa.php # CRUD program beasiswa
│   └── laporan.php         # Laporan & statistik
│
├── 📁 admin/               # Halaman admin
│   ├── dashboard.php       # Dashboard admin
│   ├── kelola_beasiswa.php # CRUD beasiswa
│   ├── kelola_pendaftaran.php # Review pendaftaran
│   ├── kelola_mahasiswa.php # Data mahasiswa
│   ├── detail_pendaftaran.php # Detail + keputusan
│   └── laporan.php         # Laporan & statistik
│
├── 📁 mahasiswa/           # Halaman mahasiswa
│   ├── dashboard.php       # Dashboard mahasiswa
│   ├── daftar_beasiswa.php # Lihat beasiswa tersedia
│   ├── form_daftar.php     # Form pendaftaran beasiswa
│   ├── riwayat.php         # Riwayat pendaftaran
│   ├── profil.php          # Edit profil
│   └── detail_pendaftaran.php
│
├── 📁 auth/                # Autentikasi
│   ├── login.php           # Login & Registrasi
│   └── logout.php          # Logout
│
├── 📁 config/
│   └── db.php              # Konfigurasi database
│
├── 📁 assets/
│   ├── 📁 css/
│   │   ├── style.css       # Global styles
│   │   ├── auth.css        # Halaman login
│   │   ├── beranda.css     # Landing page
│   │   ├── dashboard.css   # Dashboard
│   │   ├── form.css        # Form pendaftaran
│   │   ├── admin.css       # Admin-specific
│   │   ├── superadmin.css  # Superadmin-specific
│   │   └── notif.css       # Notification styles
│   └── 📁 js/
│       ├── main.js         # Landing page JS
│       ├── auth.js         # Login page JS
│       ├── dashboard.js    # Dashboard JS
│       ├── form.js         # Form JS
│       └── admin.js        # Admin AJAX JS
│
├── 📁 db/
│   └── beasiswa.sql        # Database schema + seed
│
├── 📁 uploads/             # Dokumen yang diupload (auto-created)
│
└── index.php               # Landing page (beranda)
```

---

## Fitur Sistem

### 👑 Super Admin
- ✅ Dashboard statistik global (total pengguna, admin, beasiswa, pendaftaran)
- ✅ Manajemen Admin (CRUD & aktivasi/nonaktifkan)
- ✅ Manajemen Semua Pengguna (Admin & Mahasiswa)
- ✅ Manajemen Program Beasiswa (CRUD)
- ✅ Laporan & statistik visual

### 🔐 Admin
- ✅ Dashboard statistik lengkap
- ✅ CRUD beasiswa (tambah, edit, hapus)
- ✅ Review & keputusan pendaftaran (AJAX)
- ✅ Filter & cari pendaftaran
- ✅ Kirim notifikasi otomatis ke mahasiswa
- ✅ Data mahasiswa terdaftar
- ✅ Laporan & statistik visual

### 👤 Mahasiswa
- ✅ Registrasi & Login
- ✅ Lihat beasiswa tersedia
- ✅ Daftar beasiswa (dengan upload dokumen)
- ✅ Validasi IPK & penghasilan otomatis
- ✅ Pantau status pendaftaran real-time
- ✅ Notifikasi keputusan beasiswa
- ✅ Riwayat pendaftaran
- ✅ Edit profil & ganti password

---

## Troubleshooting

**Tidak bisa login admin?**
- Pastikan database sudah di-import
- Password default: `password` (bukan `admin123`)

**Upload file gagal?**
- Pastikan folder `uploads/` ada dan writable
- Cek `upload_max_filesize` di `php.ini` (set ke 10M)
