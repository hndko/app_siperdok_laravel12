# 📄 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan

SIPERDOK adalah aplikasi web untuk mengelola pengajuan, penilaian, revisi, dan penerbitan dokumen kelayakan lingkungan secara digital.

---

## 📑 Daftar Isi

- [📄 SIPERDOK - Sistem Informasi Persetujuan Dokumen Kelayakan](#-siperdok---sistem-informasi-persetujuan-dokumen-kelayakan)
  - [📑 Daftar Isi](#-daftar-isi)
  - [⚡ Ringkasan Cepat](#-ringkasan-cepat)
  - [💡 Deskripsi Proyek](#-deskripsi-proyek)
  - [🧰 Teknologi yang Digunakan](#-teknologi-yang-digunakan)
  - [🗄️ Struktur Database](#️-struktur-database)
  - [👥 Role dan Hak Akses](#-role-dan-hak-akses)
  - [✨ Fitur Utama](#-fitur-utama)
  - [📋 Prasyarat](#-prasyarat)
  - [⚙️ Instalasi](#️-instalasi)
  - [🚀 Penggunaan](#-penggunaan)
  - [📡 REST API](#-rest-api)
  - [🧪 Pengujian](#-pengujian)
  - [🐳 Docker](#-docker)
  - [🤝 Kontribusi](#-kontribusi)
  - [📜 Lisensi](#-lisensi)

---

## ⚡ Ringkasan Cepat

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run dev
php artisan serve
```

Akses aplikasi melalui:

```text
http://127.0.0.1:8000
```

Akun demo hasil seeder:

| Role | Email | Password |
| --- | --- | --- |
| 🛡️ Admin | `admin@example.com` | `password` |
| 🧑‍💼 Pemohon | `pemohon@example.com` | `password` |
| 👨‍⚖️ Penilai | `penilai@example.com` | `password` |

---

## 💡 Deskripsi Proyek

SIPERDOK menyelesaikan kebutuhan digitalisasi proses persetujuan dokumen kelayakan lingkungan. Sistem ini membantu pemohon mengajukan dokumen, penilai melakukan evaluasi, dan admin mengelola master data serta memantau seluruh permohonan.

Alur utama aplikasi:

```text
Pemohon membuat draft
        ↓
Pemohon mengunggah dokumen dan mengirim permohonan
        ↓
Penilai meninjau dokumen
        ↓
Keputusan: disetujui, perlu revisi, atau ditolak
        ↓
Jika disetujui, sistem dapat menerbitkan surat pengesahan PDF
```

---

## 🧰 Teknologi yang Digunakan

Bahasa pemrograman dan framework utama:

- 🐘 **PHP `^8.2`** sebagai bahasa backend.
- 🚀 **Laravel `^12.0`** sebagai framework backend.
- 🟢 **JavaScript ES Module** sebagai bahasa frontend.
- 🖼️ **Vue `^3.5`** sebagai framework frontend.
- 🔁 **Inertia.js** sebagai penghubung Laravel dan Vue SPA.
- ⚡ **Vite `^7.0`** untuk bundling aset frontend.

Library backend penting:

- 🔐 **Laravel Sanctum `^4.3`** untuk autentikasi API token.
- 👥 **Spatie Laravel Permission `^6.25`** untuk role dan permission.
- 📄 **barryvdh/laravel-dompdf `^3.1`** untuk ekspor surat pengesahan PDF.
- 📊 **maatwebsite/excel `^3.1`** tersedia sebagai dependensi ekspor spreadsheet.
- 🧪 **PHPUnit `^11.5`** untuk pengujian otomatis.
- 🎨 **Laravel Pint `^1.24`** untuk formatting kode PHP.

Library frontend dan aset UI:

- 🎛️ **AdminLTE 3.2** untuk layout dashboard dan komponen admin.
- ⭐ **Font Awesome** untuk ikon antarmuka.
- 📈 **Chart.js** untuk grafik dashboard.
- 🔗 **Axios** untuk HTTP client.
- 🎨 **Tailwind CSS `^4.0`** melalui plugin Vite.

---

## 🗄️ Struktur Database

Database default pada `.env.example` menggunakan PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_siperdok_laravel12
DB_USERNAME=postgres
DB_PASSWORD=doko1337
```

Tabel inti aplikasi:

| Tabel | Fungsi Utama |
| --- | --- |
| `users` | Menyimpan akun pengguna, data kontak, NIP/NIK, nama perusahaan, dan kredensial login. |
| `document_types` | Master jenis dokumen seperti AMDAL, UKL-UPL, SPPL, PERTEK Air Limbah, dan PERTEK Emisi. |
| `projects` | Data permohonan dokumen kelayakan, pemohon, penilai, jenis dokumen, status, dan tanggal keputusan. |
| `project_documents` | Metadata dokumen yang diunggah, termasuk path file, ukuran, MIME type, dan versi dokumen. |
| `assessment_logs` | Audit trail perubahan status dan catatan evaluasi. |
| `notifications` | Notifikasi status permohonan untuk pemohon. |
| `personal_access_tokens` | Token API Laravel Sanctum. |
| `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions` | Struktur role dan permission dari Spatie Laravel Permission. |
| `sessions`, `cache`, `jobs` | Tabel pendukung Laravel untuk session, cache, queue, dan batch job. |

Relasi utama:

- `users.id` → `projects.applicant_id`
- `users.id` → `projects.evaluator_id`
- `document_types.id` → `projects.document_type_id`
- `projects.id` → `project_documents.project_id`
- `projects.id` → `assessment_logs.project_id`
- `projects.id` → `notifications.project_id`
- `users.id` → `project_documents.uploaded_by`
- `users.id` → `assessment_logs.user_id`
- `users.id` → `notifications.user_id`

Status permohonan:

| Status | Arti |
| --- | --- |
| `draft` | Permohonan masih berupa draft dan dapat diedit pemohon. |
| `submitted` | Permohonan sudah dikirim untuk dinilai. |
| `in_review` | Permohonan berada dalam proses penilaian. |
| `revision` | Penilai meminta pemohon memperbaiki dokumen. |
| `approved` | Permohonan disetujui dan dapat diterbitkan sebagai PDF. |
| `rejected` | Permohonan ditolak. |

Seeder bawaan membuat:

- 3 akun demo utama: admin, pemohon, dan penilai.
- 5 jenis dokumen lingkungan.
- 1.000 akun pemohon dan 1.000 akun penilai untuk data simulasi.
- 10.000 data permohonan dengan distribusi status berbeda.

---

## 👥 Role dan Hak Akses

| Role | Hak Akses |
| --- | --- |
| 🛡️ **Admin** | Mengakses dashboard global, melihat seluruh permohonan, mengelola master pengguna, mengelola master jenis dokumen, menilai permohonan, melihat histori, dan melakukan ekspor laporan. |
| 🧑‍💼 **Pemohon** | Membuat draft, mengunggah dokumen, mengirim permohonan, mengedit draft atau revisi, melihat status, melihat histori permohonan sendiri, dan mengunduh surat pengesahan untuk permohonan yang disetujui. |
| 👨‍⚖️ **Penilai** | Melihat daftar permohonan selain draft, membuka halaman review, memberi keputusan disetujui/revisi/ditolak, menulis catatan evaluasi, melihat histori penilaian, dan ekspor laporan. |

Permission yang didefinisikan:

```text
manage-users
manage-document-types
view-dashboard
create-project
edit-project
submit-project
review-project
approve-project
reject-project
request-revision-project
view-history
export-reports
```

---

## ✨ Fitur Utama

- 🔐 Autentikasi web berbasis session Laravel.
- 🔑 Autentikasi API berbasis Bearer Token menggunakan Sanctum.
- 🧾 Registrasi pengguna baru dengan role default `pemohon`.
- 📁 Manajemen permohonan dokumen oleh pemohon.
- ⬆️ Upload dokumen `pdf`, `doc`, `docx`, `png`, `jpg`, dan `jpeg` maksimal 10 MB.
- 🧬 Versioning dokumen ketika pemohon mengunggah revisi.
- ⚖️ Review dan keputusan penilaian oleh role `penilai` atau `admin`.
- 📝 Audit trail untuk setiap perubahan status permohonan.
- 🔔 Notifikasi otomatis setelah permohonan disetujui, diminta revisi, atau ditolak.
- 📊 Dashboard KPI dan grafik tren permohonan.
- 🧑‍💻 Master data pengguna untuk admin.
- 📚 Master jenis dokumen untuk admin.
- 📤 Ekspor laporan permohonan dalam format CSV.
- 📄 Preview dan unduh surat pengesahan PDF untuk permohonan berstatus `approved`.

---

## 📋 Prasyarat

Pastikan perangkat pengembangan memenuhi kebutuhan minimum berikut:

- 🐘 PHP `>= 8.2`
- 📦 Composer `>= 2.6`
- 🟢 Node.js `>= 18`
- 🧶 NPM `>= 9`
- 🗄️ PostgreSQL `>= 15` atau database lain yang didukung Laravel
- 🧰 Git

Ekstensi PHP yang umum dibutuhkan:

```text
pdo
pdo_pgsql
pdo_mysql
mbstring
gd
zip
bcmath
exif
pcntl
```

---

## ⚙️ Instalasi

1. Clone repository:

```bash
git clone <url-repository-anda>
cd app_siperdok_laravel12
```

2. Pasang dependensi PHP:

```bash
composer install
```

3. Pasang dependensi frontend:

```bash
npm install
```

4. Buat file environment:

```bash
cp .env.example .env
```

Untuk PowerShell di Windows:

```powershell
Copy-Item .env.example .env
```

5. Generate application key:

```bash
php artisan key:generate
```

6. Sesuaikan konfigurasi database di `.env`, lalu jalankan migrasi dan seeder:

```bash
php artisan migrate:fresh --seed
```

7. Buat symbolic link storage untuk file upload:

```bash
php artisan storage:link
```

8. Jalankan Vite untuk pengembangan frontend:

```bash
npm run dev
```

9. Jalankan server Laravel:

```bash
php artisan serve
```

---

## 🚀 Penggunaan

Akses halaman utama:

```text
http://127.0.0.1:8000
```

Login menggunakan salah satu akun demo:

```text
admin@example.com / password
pemohon@example.com / password
penilai@example.com / password
```

Contoh alur pemohon:

1. Login sebagai `pemohon@example.com`.
2. Buka menu permohonan.
3. Buat permohonan baru.
4. Pilih jenis dokumen.
5. Unggah dokumen pendukung.
6. Simpan sebagai draft atau kirim untuk penilaian.
7. Jika status menjadi `revision`, edit permohonan dan unggah dokumen versi baru.

Contoh alur penilai:

1. Login sebagai `penilai@example.com`.
2. Buka menu penilaian.
3. Pilih permohonan yang masuk.
4. Buka halaman review.
5. Berikan keputusan `approved`, `revision`, atau `rejected`.
6. Isi catatan evaluasi.

Build aset production:

```bash
npm run build
```

---

## 📡 REST API

Login API:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"pemohon@example.com","password":"password"}'
```

Ambil daftar permohonan:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/projects \
  -H "Authorization: Bearer <TOKEN_ANDA>"
```

Buat permohonan baru:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/projects \
  -H "Authorization: Bearer <TOKEN_ANDA>" \
  -F "title=Pembangunan Gedung Baru" \
  -F "document_type_id=1" \
  -F "description=Pengajuan dokumen kelayakan lingkungan." \
  -F "submit_action=submit" \
  -F "document=@/path/to/file.pdf"
```

Proses penilaian:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/assessments/1 \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>" \
  -H "Content-Type: application/json" \
  -d '{"decision":"approved","notes":"Dokumen lengkap dan memenuhi syarat."}'
```

Ambil histori penilaian:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/assessments/history \
  -H "Authorization: Bearer <TOKEN_ANDA>"
```

Ambil master jenis dokumen:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/document-types \
  -H "Authorization: Bearer <TOKEN_ANDA>"
```

Logout API:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/logout \
  -H "Authorization: Bearer <TOKEN_ANDA>"
```

---

## 🧪 Pengujian

Jalankan semua test:

```bash
php artisan test
```

Test yang tersedia mencakup:

- Render halaman login.
- Login web.
- Login API Sanctum.
- Alur pembuatan permohonan, revisi, submit ulang, dan approval.

---

## 🐳 Docker

Proyek menyediakan `Dockerfile` dan `docker-compose.yml` untuk menjalankan service PHP-FPM, Nginx, PostgreSQL, dan Redis.

```bash
docker-compose up -d --build
```

Port yang digunakan:

| Service | Port |
| --- | --- |
| Nginx | `8080:80` |
| PostgreSQL | `5432:5432` |
| Redis | `6379:6379` |
| PHP-FPM | `9000` |

Catatan: `docker-compose.yml` mengacu ke folder konfigurasi Nginx `./docker-compose/nginx/conf.d/`. Pastikan konfigurasi Nginx tersedia sebelum memakai Docker untuk menjalankan webserver.

---

## 🤝 Kontribusi

Kontributor yang ingin mengembangkan proyek ini disarankan mengikuti aturan berikut:

1. Buat branch baru dari branch utama.
2. Gunakan nama branch yang jelas, misalnya `feature/manajemen-notifikasi` atau `fix/api-authorization`.
3. Jalankan test sebelum mengirim perubahan.
4. Hindari mengubah struktur database tanpa migration.
5. Tulis pesan commit yang ringkas dan menjelaskan tujuan perubahan.
6. Buka Pull Request atau Merge Request untuk proses review.

Perintah dasar kontribusi:

```bash
git checkout -b feature/nama-fitur
php artisan test
git add .
git commit -m "feat: menambahkan nama fitur"
git push origin feature/nama-fitur
```

---

## 📜 Lisensi

Proyek ini menggunakan lisensi **MIT** sesuai konfigurasi `composer.json`.

Copyright © 2026 SIPERDOK.
