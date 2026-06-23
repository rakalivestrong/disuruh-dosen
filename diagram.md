# Diagram Aktivitas Sistem Beasiswa

Berikut adalah diagram aktivitas sistem beasiswa yang dibagi menjadi 3 berdasarkan hak akses (Role) agar gambar tidak pecah saat diexport menjadi PNG.

---

## 1. Halaman Aktivitas - Mahasiswa

![Diagram Aktivitas Mahasiswa](assets/img/diagram_mahasiswa.png)

```mermaid
flowchart TD
    %% Main Flow Initiation
    Start([Mulai]) --> Index["index.php<br>(Halaman Utama/Landing)"]
    Index --> Login["auth/login.php<br>(Form Login)"]
    Login --> DecRole{"Cek Hak Akses / Role"}
    DecRole -->|Role: Mahasiswa| M_Dash["mahasiswa/dashboard.php<br>(Dashboard Mahasiswa)"]

    %% Subgraph Mahasiswa
    subgraph Mahasiswa ["Aktor: Mahasiswa"]
        M_Dash --> M_Prof["mahasiswa/profil.php<br>(Lihat/Edit Profil)"]
        M_Dash --> M_Beasiswa["mahasiswa/daftar_beasiswa.php<br>(Daftar Program Beasiswa)"]
        M_Beasiswa --> M_Form["mahasiswa/form_daftar.php<br>(Form Daftar & Upload Berkas)"]
        M_Form --> M_Riwayat["mahasiswa/riwayat.php<br>(Riwayat Pendaftaran)"]
        M_Dash --> M_Riwayat
        M_Riwayat --> M_Detail["mahasiswa/detail_pendaftaran.php<br>(Detail Status Pendaftaran)"]
    end

    %% Logout Flow
    Logout["auth/logout.php<br>(Hapus Session & Keluar)"]
    M_Prof --> Logout
    M_Riwayat --> Logout
    Logout --> Stop([Selesai])

    %% Styling Definitions
    classDef startEnd fill:none,stroke:#16A34A,stroke-width:2px,color:#16A34A,font-weight:bold;
    classDef auth fill:none,stroke:#3B82F6,stroke-width:1.5px,color:#3B82F6;
    classDef decision fill:none,stroke:#D97706,stroke-width:2px,color:#D97706,font-weight:bold;
    classDef flowNode fill:none,stroke:#64748B,stroke-width:1px,color:#334155;

    %% Assigning Classes to Nodes
    class Start,Stop startEnd;
    class Index,Login,Logout auth;
    class DecRole decision;
    class M_Dash,M_Prof,M_Beasiswa,M_Form,M_Riwayat,M_Detail flowNode;

    %% Subgraph Styling
    style Mahasiswa fill:none,stroke:#CBD5E1,stroke-width:1.5px,stroke-dasharray: 4 4;
```

---

## 2. Halaman Aktivitas - Admin / Staf

![Diagram Aktivitas Admin / Staf](assets/img/diagram_admin.png)

```mermaid
flowchart TD
    %% Main Flow Initiation
    Start([Mulai]) --> Index["index.php<br>(Halaman Utama/Landing)"]
    Index --> Login["auth/login.php<br>(Form Login)"]
    Login --> DecRole{"Cek Hak Akses / Role"}
    DecRole -->|Role: Admin| A_Dash["admin/dashboard.php<br>(Dashboard Admin)"]

    %% Subgraph Admin
    subgraph Admin ["Aktor: Admin / Staf"]
        A_Dash --> A_Mhs["admin/kelola_mahasiswa.php<br>(Kelola Data Mahasiswa)"]
        A_Dash --> A_Bea["admin/kelola_beasiswa.php<br>(Kelola Kategori Beasiswa)"]
        A_Dash --> A_Daf["admin/kelola_pendaftaran.php<br>(Validasi Pendaftaran)"]
        A_Dash --> A_Lap["admin/laporan.php<br>(Cetak & Unduh Laporan)"]
        
        A_Daf --> A_Det["admin/detail_pendaftaran.php<br>(Review Berkas Pendaftar)"]
        A_Det --> DecValid{"Apakah Berkas &<br>Syarat Valid?"}
        DecValid -->|Ya| A_Acc["Setujui Pendaftaran<br>(Status: Diterima)"]
        DecValid -->|Tidak| A_Rej["Tolak Pendaftaran<br>(Status: Ditolak)"]
    end

    %% Logout Flow
    Logout["auth/logout.php<br>(Hapus Session & Keluar)"]
    A_Lap --> Logout
    A_Acc --> Logout
    A_Rej --> Logout
    Logout --> Stop([Selesai])

    %% Styling Definitions
    classDef startEnd fill:none,stroke:#16A34A,stroke-width:2px,color:#16A34A,font-weight:bold;
    classDef auth fill:none,stroke:#3B82F6,stroke-width:1.5px,color:#3B82F6;
    classDef decision fill:none,stroke:#D97706,stroke-width:2px,color:#D97706,font-weight:bold;
    classDef flowNode fill:none,stroke:#64748B,stroke-width:1px,color:#334155;
    classDef successNode fill:none,stroke:#10B981,stroke-width:1.5px,color:#065F46,font-weight:bold;
    classDef dangerNode fill:none,stroke:#EF4444,stroke-width:1.5px,color:#991B1B,font-weight:bold;

    %% Assigning Classes to Nodes
    class Start,Stop startEnd;
    class Index,Login,Logout auth;
    class DecRole,DecValid decision;
    class A_Dash,A_Mhs,A_Bea,A_Daf,A_Det,A_Lap flowNode;
    class A_Acc successNode;
    class A_Rej dangerNode;

    %% Subgraph Styling
    style Admin fill:none,stroke:#CBD5E1,stroke-width:1.5px,stroke-dasharray: 4 4;
```

---

## 3. Halaman Aktivitas - Superadmin

![Diagram Aktivitas Superadmin](assets/img/diagram_superadmin.png)

```mermaid
flowchart TD
    %% Main Flow Initiation
    Start([Mulai]) --> Index["index.php<br>(Halaman Utama/Landing)"]
    Index --> Login["auth/login.php<br>(Form Login)"]
    Login --> DecRole{"Cek Hak Akses / Role"}
    DecRole -->|Role: Superadmin| S_Dash["superadmin/dashboard.php<br>(Dashboard Superadmin)"]

    %% Subgraph Superadmin
    subgraph Superadmin ["Aktor: Superadmin"]
        S_Dash --> S_Adm["superadmin/kelola_admin.php<br>(Kelola Akun Admin)"]
        S_Dash --> S_Usr["superadmin/kelola_users.php<br>(Kelola Semua User)"]
        S_Dash --> S_Bea["superadmin/kelola_beasiswa.php<br>(Kelola Data Beasiswa)"]
        S_Dash --> S_Lap["superadmin/laporan.php<br>(Cetak Laporan Global)"]
    end

    %% Logout Flow
    Logout["auth/logout.php<br>(Hapus Session & Keluar)"]
    S_Lap --> Logout
    Logout --> Stop([Selesai])

    %% Styling Definitions
    classDef startEnd fill:none,stroke:#16A34A,stroke-width:2px,color:#16A34A,font-weight:bold;
    classDef auth fill:none,stroke:#3B82F6,stroke-width:1.5px,color:#3B82F6;
    classDef decision fill:none,stroke:#D97706,stroke-width:2px,color:#D97706,font-weight:bold;
    classDef flowNode fill:none,stroke:#64748B,stroke-width:1px,color:#334155;

    %% Assigning Classes to Nodes
    class Start,Stop startEnd;
    class Index,Login,Logout auth;
    class DecRole decision;
    class S_Dash,S_Adm,S_Usr,S_Bea,S_Lap flowNode;

    %% Subgraph Styling
    style Superadmin fill:none,stroke:#CBD5E1,stroke-width:1.5px,stroke-dasharray: 4 4;
```