# SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan

**SIPERDOK** adalah aplikasi berbasis web yang dibangun menggunakan **Laravel 12**, **AdminLTE 3.2.0**, dan **PostgreSQL** untuk mengelola seluruh tahapan proses pengajuan, verifikasi administrasi, hingga keputusan akhir kelayakan dokumen (*Approved*, *Revision*, *Rejected*) secara terstruktur, cepat, dan aman.

System ini dirancang dengan arsitektur berkinerja tinggi yang mampu menangani puluhan ribu data permohonan dokumen (*10.000+ data proyek*) beserta riwayat penilaian audit trail secara efisien.

---

## 📐 Alur Bisnis Sistem (Business Flow)

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

## 🛠️ Technology Stack & Dependencies

1. **Backend Framework**: PHP 8.2+ / Laravel 12
2. **Database**: PostgreSQL / SQLite / MySQL (Optimized with Database Indexes for 10.000+ records)
3. **Frontend UI Template**: AdminLTE 3.2.0 (Bootstrap 4, FontAwesome 5, jQuery)
4. **Authentication & Security**: Laravel Sanctum (Session & REST API Token)
5. **Role & Permission**: Spatie `laravel-permission` (`admin`, `pemohon`, `penilai`)
6. **Analytics & Charts**: Chart.js (Monthly Submissions Trend & Status Breakdown)
7. **Exporting**: `barryvdh/laravel-dompdf` (PDF Surat Pengesahan) & CSV/Excel Exports
8. **Containerization & CI/CD**: Docker, Docker Compose, GitLab CI, GitHub Actions

---

## 🔑 Hak Akses User & Demo Credentials

Password default untuk seluruh akun demo adalah: **`password`**

| Role User | Email Demo | Hak Akses Utama |
| :--- | :--- | :--- |
| **Pemohon Dokumen** | `pemohon@example.com` | Dashboard Pemohon, Create Project, Upload Berkas, Edit Draft, Perbaiki Dokumen (Revision), Submit Ulang, Lihat Status & History. |
| **Penilai Dokumen** | `penilai@example.com` | Dashboard Penilai, Review Seluruh Permohonan, Beri Catatan Evaluasi, Setuju (Approve), Request Revisi, Tolak (Reject), Histori Log. |
| **Administrator** | `admin@example.com` | Full Master Data (User Management, Document Types, Audit Trail, Dashboard Monitoring). |

---

## 🚀 Petunjuk Menjalankan Project (Installation Guide)

### 1. Clone & Setup Environment
```bash
git clone <URL_REPOSITORY_GITLAB>
cd app_siperdok_laravel12
```

### 2. Install Dependency Composer & NPM
```bash
composer install
```

### 3. Konfigurasi File `.env`
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

*Secara default `.env` dikonfigurasi menggunakan SQLite / PostgreSQL. Jika menggunakan PostgreSQL, pastikan kredensial `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai.*

### 4. Jalankan Migrasi & Database Seeder (10.000 Proyek + 2.000 Users)
Jalankan perintah berikut untuk membuat struktur tabel dan mengisi **10.000 Data Proyek** dan **2.000 Users**:
```bash
php artisan migrate:fresh --seed
```

### 5. Buat Symbolic Link Storage (Untuk Upload Berkas)
```bash
php artisan storage:link
```

### 6. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi kini dapat diakses melalui browser di: **`http://127.0.0.1:8000`**

---

## 🧪 Menjalankan Automated Feature & Unit Tests

Proyek ini dilengkapi dengan Feature Tests lengkap untuk menguji otentikasi dan alur bisnis persetujuan dokumen:
```bash
php artisan test
```

---

## 🐳 Menjalankan dengan Docker

```bash
docker-compose up -d --build
```
Aplikasi akan berjalan pada port `8080` (http://localhost:8080).

---

## 📡 REST API Endpoints (Laravel Sanctum)

| Method | Endpoint | Deskripsi | Authentication |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/login` | Login user & dapatkan Sanctum Bearer Token | Public |
| `GET` | `/api/v1/projects` | Daftar permohonan dokumen (dengan filter status) | Bearer Token |
| `GET` | `/api/v1/projects/{id}` | Detail permohonan dokumen & audit trail | Bearer Token |
| `POST` | `/api/v1/assessments/{id}` | Penilaian permohonan (Approve, Revision, Reject) | Bearer Token (Penilai/Admin) |
