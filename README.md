# 📄 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan

SIPERDOK adalah aplikasi web berbasis Laravel 12, Vue 3, dan AdminLTE 3.2.0 yang dirancang untuk mengelola seluruh tahapan permohonan dokumen kelayakan lingkungan hidup secara terstruktur, transparan, dan berkinerja tinggi.

---

## 📑 Daftar Isi

- [📄 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan](#-siperdok---sistem-informasi-persetujuan-dokumen-kelayakan)
  - [📑 Daftar Isi](#-daftar-isi)
  - [💡 Deskripsi Proyek](#-deskripsi-proyek)
    - [🎯 Fitur Utama](#-fitur-utama)
    - [📐 Alur Bisnis Sistem (Business Workflow)](#-alur-bisnis-sistem-business-workflow)
  - [📋 Prasyarat](#-prasyarat)
  - [⚙️ Instalasi](#️-instalasi)
  - [🚀 Penggunaan](#-penggunaan)
    - [🔑 Akun Demo \& Hak Akses](#-akun-demo--hak-akses)
    - [🧪 Menjalankan Feature Tests](#-menjalankan-feature-tests)
    - [📡 Penggunaan REST API (Sanctum)](#-penggunaan-rest-api-sanctum)
    - [🐳 Menjalankan via Docker](#-menjalankan-via-docker)
  - [🤝 Kontribusi](#-kontribusi)
  - [📜 Lisensi](#-lisensi)

---

## 💡 Deskripsi Proyek

Seiring pesatnya pertumbuhan pengajuan izin kelayakan setiap tahunnya, instansi pemerintah memerlukan sistem pendokumentasian yang mampu menangani ratusan ribu data permohonan beserta riwayat audit penilaiannya secara cepat dan responsif.

**SIPERDOK** memecahkan masalah lambatnya verifikasi manual melalui otomatisasi alur kerja digital mulai dari pembuatan draft permohonan, unggah berkas, verifikasi administrasi, hingga penerbitan Surat Pengesahan Dokumen Kelayakan berbasis PDF.

### 🎯 Fitur Utama

- 🔐 **Autentikasi & Multi-Role Access**: Pemisahan hak akses fleksibel berbasis Spatie Permission (`Pemohon`, `Penilai`, `Admin`) dengan layout terpisah `app-auth` & `app-modules`.
- 💚 **Frontend Vue 3 Reaktif**: Komponen reaktif Vue 3 Single File Components (`StatusBadge.vue`, `DecisionModal.vue`) terintegrasi dengan Vite bundler.
- 📁 **Manajemen Permohonan & Berkas**: Pembuatan draft, upload dokumen dengan validasi format & ukuran (PDF/Docx/Image max 10MB), serta versioning berkas permohonan.
- ⚖️ **Modul Penilaian & Keputusan**: Panel penilai untuk keputusan **Setuju (Approved)**, **Revisi (Revision)**, dan **Ditolak (Rejected)** dilengkapi catatan evaluasi dan notifikasi otomatis.
- 📑 **Audit Trail & Histori Penilaian**: Catatan riwayat kronologis lengkap untuk setiap aksi perubahan status permohonan.
- 📊 **Dashboard Analitik Interactive**: Visualisasi tren bulanan dan sebaran status permohonan menggunakan Chart.js.
- 🖨️ **Export PDF & Excel/CSV**: Penerbitan Surat Pengesahan Dokumen Kelayakan (PDF) serta export laporan daftar permohonan (CSV/Excel).
- ⚡ **Performa & Eager Loading**: Bebas masalah N+1 query dengan eager loading teroptimasi dan *batch seeder* 10.000+ data proyek & 2.000 user pada PostgreSQL `db_siperdok_laravel12`.

### 📐 Alur Bisnis Sistem (Business Workflow)

```
[PEMOHON]                                        [PENGUJI / PENILAI]
  │                                                      │
  ├─ 1. Login & Dashboard                                │
  ├─ 2. Melengkapi Form Pengajuan & Upload Berkas        │
  ├─ 3. SUBMIT / Kirim Permohonan ───────────────────────┼─► 4. Menerima Notifikasi & Dokumen
  │                                                      │    │
  │                                                      ├─► 5. Penilaian / Review Dokumen
  │                                                      │    │
  │   ◄── REVISI (Catatan & Status Revision) ────────────┼────┴─► Keputusan Penilaian?
  ├─ 6a. Perbaiki Dokumen & Submit Ulang ────────────────┤         ├─ 1. SETUJU (Approved) ──► Pengesahan Dokumen ──► Dokumen Terbit & Notifikasi
  │                                                      │         ├─ 2. REVISI (Revision) ──► Notifikasi Revisi ke Pemohon
  │   ◄── DITOLAK (Status Rejected) ─────────────────────┼─────────┴─ 3. DITOLAK (Rejected) ──► Notifikasi Penolakan ke Pemohon
  └─ 6b. Melakukan Pengajuan Baru ───────────────────────┘
```

---

## 📋 Prasyarat

Sebelum memasang dan menjalankan proyek ini, pastikan perangkat Anda memenuhi spesifikasi minimum berikut:

- 🐘 **PHP**: versi `>= 8.2` (dengan ekstensi `pdo`, `pdo_pgsql`/`pdo_mysql`/`pdo_sqlite`, `gd`, `zip`, `mbstring` aktif).
- 📦 **Composer**: versi `>= 2.6.0`.
- 🟢 **Node.js**: versi `>= 18.0.0` & **NPM** `>= 9.0.0`.
- 🗄️ **Database**: PostgreSQL `>= 15.0` (Nama Database: `db_siperdok_laravel12`, Port: `5432`, Password: `doko1337`).
- 🧰 **Git**: versi `>= 2.40.0`.

---

## ⚙️ Instalasi

Ikuti langkah-langkah berbasis perintah terminal berikut untuk memasang proyek di lingkungan lokal Anda:

1. **Clone Repository Proyek**
   ```bash
   git clone https://gitlab.com/username/app_siperdok_laravel12.git
   cd app_siperdok_laravel12
   ```

2. **Pasang Dependensi PHP & NPM**
   ```bash
   composer install
   npm install
   ```

3. **Kompilasi Aset Frontend (Vue 3 + Vite)**
   ```bash
   npm run build
   ```

4. **Konfigurasi Environment (.env)**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database & Seeder Dataset (10.000 Proyek & 2.000 Users)**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Buat Symbolic Link Storage (Untuk Berkas Upload)**
   ```bash
   php artisan storage:link
   ```

---

## 🚀 Penggunaan

### 🔑 Akun Demo & Hak Akses

Password default untuk seluruh akun demo adalah: **`password`**

| Role User | Email Login | Hak Akses Utama |
| :--- | :--- | :--- |
| 🧑‍💻 **Pemohon** | `pemohon@example.com` | Dashboard Pemohon, Pengajuan Proyek Baru, Upload Berkas, Edit Draft, Submit Ulang Revisi, Unduh Sertifikat PDF. |
| 👨‍⚖️ **Penilai** | `penilai@example.com` | Dashboard Penilai, Review Permohonan (Vue 3 Component), Input Decision (Approve / Revision / Reject), Catatan Evaluasi, Audit Log. |
| 🛡️ **Admin** | `admin@example.com` | Master Data Users, Master Jenis Dokumen, Laporan Global & Dashboard Monitoring. |

### 💻 Menjalankan Server Aplikasi

Jalankan perintah berikut pada terminal untuk mengaktifkan web server lokal:
```bash
php artisan serve
```
Akses aplikasi melalui browser pada tautan: **`http://127.0.0.1:8000`**

### 🧪 Menjalankan Feature Tests

Proyek ini telah dilengkapi dengan pengujian otomatis (*Automated PHPUnit Tests*) untuk otentikasi dan alur persetujuan permohonan:
```bash
php artisan test
```

### 📡 Penggunaan REST API (Sanctum)

Aplikasi menyediakan API RESTful untuk integrasi aplikasi mobile atau SPA:

```bash
# 1. Authenticaton Login (Mendapatkan Bearer Token)
curl -X POST http://127.0.0.1:8000/api/v1/login \
     -H "Content-Type: application/json" \
     -d '{"email":"pemohon@example.com","password":"password"}'

# 2. Mendapatkan Daftar Permohonan Dokumen
curl -X GET http://127.0.0.1:8000/api/v1/projects \
     -H "Authorization: Bearer <TOKEN_ANDA>"

# 3. Memproses Penilaian Dokumen (Role Penilai/Admin)
curl -X POST http://127.0.0.1:8000/api/v1/assessments/1 \
     -H "Authorization: Bearer <TOKEN_ANDA>" \
     -H "Content-Type: application/json" \
     -d '{"decision":"approved","notes":"Dokumen disetujui."}'
```

### 🐳 Menjalankan via Docker

Untuk menjalankan proyek dalam kontainer Docker:
```bash
docker-compose up -d --build
```
Aplikasi akan aktif dan dapat diakses pada port **`8080`** (`http://localhost:8080`).

---

## 🤝 Kontribusi

Kami menyambut baik kontribusi untuk pengembangan dan perbaikan kode SIPERDOK. Langkah-langkah kontribusi:

1. **Fork** repository ini.
2. Buat *feature branch* baru (`git checkout -b feature/FiturBaru`).
3. Lakukan **Commit** perubahan Anda dengan pesan yang jelas (`git commit -m 'feat: menambahkan fitur X'`).
4. **Push** ke branch Anda (`git push origin feature/FiturBaru`).
5. Buat **Pull Request / Merge Request** untuk ditinjau oleh tim pengembang utama.

---

## 📜 Lisensi

Proyek ini dilindungi di bawah lisensi **MIT License**. Anda bebas menggunakan, mengubah, dan mendistribusikan perangkat lunak ini sesuai dengan ketentuan lisensi.

Copyright © 2026 **SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan**.
