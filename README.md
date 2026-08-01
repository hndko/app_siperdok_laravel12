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
  - [🧭 Struktur Frontend](#-struktur-frontend)
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
Penilai mengisi checklist verifikasi administrasi
        ↓
Keputusan: disetujui, perlu revisi, atau ditolak
        ↓
Jika disetujui, penilai/admin menerbitkan certificate dan PDF resmi
```

---

## 🧰 Teknologi yang Digunakan

Bahasa pemrograman dan framework utama:

- 🐘 **PHP `^8.2`** sebagai bahasa backend.
- 🚀 **Laravel `^12.0`** sebagai framework backend.
- 🟢 **JavaScript ES Module** sebagai bahasa frontend.
- 🖼️ **Vue `^3.5`** sebagai framework frontend.
- 🧭 **Vue Router** untuk routing halaman SPA di sisi frontend.
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
- 🔗 **Axios** untuk HTTP client REST API dengan Bearer Token Sanctum.
- 🔔 **SweetAlert2** untuk toast notifikasi dan modal konfirmasi.
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
| `verification_checklist_items` | Master checklist verifikasi administrasi yang wajib/opsional untuk proses penilaian. |
| `project_verification_checklists` | Hasil checklist setiap permohonan, status item, catatan penilai, reviewer, dan waktu pengecekan. |
| `assessment_logs` | Audit trail perubahan status dan catatan evaluasi. |
| `notifications` | Notifikasi status permohonan untuk pemohon, termasuk status sudah dibaca dan waktu baca. |
| `certificate_counters` | Counter nomor certificate per tahun dan bulan agar nomor terbit berurutan. |
| `personal_access_tokens` | Token API Laravel Sanctum. |
| `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions` | Struktur role dan permission dari Spatie Laravel Permission. |
| `sessions`, `cache`, `jobs` | Tabel pendukung Laravel untuk session, cache, queue, dan batch job. |

Relasi utama:

- `users.id` → `projects.applicant_id`
- `users.id` → `projects.evaluator_id`
- `document_types.id` → `projects.document_type_id`
- `users.id` → `projects.certificate_issued_by`
- `projects.id` → `project_documents.project_id`
- `projects.id` → `project_verification_checklists.project_id`
- `verification_checklist_items.id` → `project_verification_checklists.checklist_item_id`
- `users.id` → `project_verification_checklists.reviewer_id`
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
| `approved` | Permohonan disetujui dan siap diterbitkan menjadi certificate. |
| `certificate_issued` | Certificate sudah disahkan, memiliki nomor terbit, dan PDF resmi dapat diunduh. |
| `rejected` | Permohonan ditolak. |

Seeder bawaan membuat:

- 3 akun demo utama: admin, pemohon, dan penilai.
- 5 jenis dokumen lingkungan.
- Master checklist verifikasi administrasi.
- 1.000 akun pemohon dan 1.000 akun penilai untuk data simulasi.
- 10.000 data permohonan dengan distribusi status berbeda.

---

## 👥 Role dan Hak Akses

| Role | Hak Akses |
| --- | --- |
| 🛡️ **Admin** | Mengakses dashboard global, melihat seluruh permohonan, mengelola master pengguna, mengelola master jenis dokumen, menilai permohonan, melihat histori, dan melakukan ekspor laporan. |
| 🧑‍💼 **Pemohon** | Membuat draft, mengunggah dokumen, mengirim permohonan, mengedit draft atau revisi, melihat status, melihat histori permohonan sendiri, menerima notifikasi, dan mengunduh PDF resmi setelah certificate diterbitkan. |
| 👨‍⚖️ **Penilai** | Melihat daftar permohonan selain draft, membuka halaman review, mengisi checklist verifikasi administrasi, memberi keputusan disetujui/revisi/ditolak, menerbitkan certificate setelah approved, melihat histori penilaian, dan ekspor laporan. |

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

- 🔑 Autentikasi REST API berbasis Bearer Token menggunakan Laravel Sanctum.
- 🧭 Web route hanya berfungsi sebagai SPA fallback untuk render halaman Vue.
- 🧾 Registrasi pengguna baru dengan role default `pemohon`.
- 👤 Edit profil pengguna dan ubah password dengan verifikasi password saat ini.
- 📁 Manajemen permohonan dokumen oleh pemohon.
- ⬆️ Upload dokumen `pdf`, `doc`, `docx`, `png`, `jpg`, dan `jpeg` maksimal 10 MB.
- 🧬 Versioning dokumen ketika pemohon mengunggah revisi.
- ⚖️ Review dan keputusan penilaian oleh role `penilai` atau `admin`.
- ✅ Checklist verifikasi administrasi sebelum keputusan final.
- 🏷️ Penerbitan certificate setelah permohonan berstatus `approved`.
- 📄 PDF certificate resmi hanya tersedia setelah status `certificate_issued`.
- 🔎 Verifikasi publik nomor certificate dengan endpoint throttled.
- 📝 Audit trail untuk setiap perubahan status permohonan.
- 🔔 Notifikasi otomatis setelah permohonan diproses, dengan fitur tandai dibaca dan tandai semua dibaca.
- 📊 Dashboard KPI dan grafik tren permohonan.
- 🧑‍💻 Master data pengguna untuk admin.
- 📚 Master jenis dokumen untuk admin.
- 📤 Ekspor laporan permohonan dalam format CSV dan Excel `.xlsx`.
- 📄 Preview dan unduh surat pengesahan PDF untuk permohonan berstatus `approved`.
- 🔔 Toast untuk notifikasi sukses/gagal dan SweetAlert untuk konfirmasi aksi penting.

---

## 🧭 Struktur Frontend

Halaman Vue disusun berdasarkan domain agar konsisten dengan struktur REST API:

```text
resources/js/pages/
├── Auth/
│   ├── Login.vue
│   ├── Profile.vue
│   └── Register.vue
└── Modules/
    ├── Assessments/
    ├── Dashboard.vue
    ├── Exports/
    ├── Master/
    └── projects/
```

Aturan struktur:

- `Auth` berada di luar `Modules` karena autentikasi adalah domain khusus.
- Semua halaman fitur seperti dashboard, project, assessment, export, dan master data berada di dalam `Modules`.
- Routing halaman dikelola oleh Vue Router di `resources/js/router.js`.
- `routes/web.php` hanya menyajikan shell `resources/views/app.blade.php` sebagai fallback SPA.
- Komponen Vue mengambil data, memproses filter, submit form, logout, dan export melalui REST API `/api/v1`.

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

Login dan register pada antarmuka Vue menggunakan REST API:

- `POST /api/v1/login`
- `POST /api/v1/register`
- Token Sanctum disimpan di `localStorage` sebagai `siperdok_token`.
- Axios otomatis mengirim header `Authorization: Bearer <TOKEN>` untuk request berikutnya.

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
5. Isi checklist verifikasi administrasi.
6. Berikan keputusan `approved`, `revision`, atau `rejected`.
7. Jika status sudah `approved`, terbitkan certificate.

Build aset production:

```bash
npm run build
```

---

## 📡 REST API

Semua aksi bisnis aplikasi berjalan melalui REST API prefix `/api/v1`. Web route hanya menjadi fallback untuk membuka halaman Vue.

Register API:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Pemohon Baru",
    "email":"pemohon_baru@example.com",
    "phone":"08123456789",
    "nip_nik":"3171000011112222",
    "company_name":"PT Contoh Baru",
    "password":"password",
    "password_confirmation":"password"
  }'
```

Login API:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"pemohon@example.com","password":"password"}'
```

Ambil user aktif, role, dan notifikasi:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/me \
  -H "Authorization: Bearer <TOKEN_ANDA>"
```

Update profil pengguna:

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/profile \
  -H "Authorization: Bearer <TOKEN_ANDA>" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Nama Baru",
    "email":"nama.baru@example.com",
    "phone":"08123456789",
    "nip_nik":"3171000011112222",
    "company_name":"PT Contoh"
  }'
```

Ubah password profil:

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/profile \
  -H "Authorization: Bearer <TOKEN_ANDA>" \
  -H "Content-Type: application/json" \
  -d '{
    "name":"Nama Baru",
    "email":"nama.baru@example.com",
    "current_password":"password_lama",
    "password":"password_baru",
    "password_confirmation":"password_baru"
  }'
```

Ambil data dashboard:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/dashboard \
  -H "Authorization: Bearer <TOKEN_ANDA>"
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

Update permohonan draft/revisi:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/projects/1 \
  -H "Authorization: Bearer <TOKEN_ANDA>" \
  -F "title=Pembangunan Gedung Baru Revisi" \
  -F "document_type_id=1" \
  -F "description=Perbaikan data pengajuan." \
  -F "submit_action=submit"
```

Hapus draft permohonan:

```bash
curl -X DELETE http://127.0.0.1:8000/api/v1/projects/1 \
  -H "Authorization: Bearer <TOKEN_ANDA>"
```

Mulai review permohonan:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/assessments/1/start-review \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>" \
  -H "Content-Type: application/json" \
  -d '{"notes":"Review administrasi dimulai."}'
```

Ambil checklist verifikasi administrasi:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/projects/1/verification-checklists \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>"
```

Simpan checklist verifikasi administrasi:

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/projects/1/verification-checklists \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>" \
  -H "Content-Type: application/json" \
  -d '{
    "items":[
      {"checklist_item_id":1,"status":"passed","notes":"Dokumen lengkap."},
      {"checklist_item_id":2,"status":"passed","notes":"Jenis dokumen sesuai."}
    ]
  }'
```

Proses penilaian:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/assessments/1 \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>" \
  -H "Content-Type: application/json" \
  -d '{"decision":"approved","notes":"Dokumen lengkap dan memenuhi syarat."}'
```

Terbitkan certificate setelah permohonan disetujui:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/projects/1/issue-certificate \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>"
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

Ambil master pengguna khusus admin:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/users \
  -H "Authorization: Bearer <TOKEN_ADMIN>"
```

Ambil notifikasi dan tandai sudah dibaca:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/notifications \
  -H "Authorization: Bearer <TOKEN_ANDA>"

curl -X PATCH http://127.0.0.1:8000/api/v1/notifications/1/read \
  -H "Authorization: Bearer <TOKEN_ANDA>"

curl -X PATCH http://127.0.0.1:8000/api/v1/notifications/read-all \
  -H "Authorization: Bearer <TOKEN_ANDA>"
```

Export laporan:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/exports/projects/csv \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>" \
  -o laporan.csv

curl -X GET http://127.0.0.1:8000/api/v1/exports/projects/xlsx \
  -H "Authorization: Bearer <TOKEN_ADMIN_ATAU_PENILAI>" \
  -o laporan.xlsx
```

Download PDF certificate resmi:

```bash
curl -X GET http://127.0.0.1:8000/api/v1/exports/projects/1/certificate \
  -H "Authorization: Bearer <TOKEN_ANDA>" \
  -o surat-pengesahan.pdf
```

Verifikasi publik nomor certificate:

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/certificates/verify/CERT/2026/08/000001"
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
- Login dan register API Sanctum.
- Proteksi akses REST API berdasarkan role.
- Alur pembuatan permohonan, checklist administrasi, revisi, submit ulang, approval, dan penerbitan certificate melalui API.
- Validasi approval wajib menunggu checklist administrasi selesai.
- Validasi PDF certificate hanya tersedia setelah certificate diterbitkan.
- Endpoint notifikasi untuk tandai dibaca.
- Validasi route controller invokable.
- Export Excel.

Validasi tambahan yang disarankan sebelum commit:

```bash
php artisan route:list
npm run build
docker compose config
```

CI/CD sederhana tersedia untuk GitHub Actions dan GitLab CI:

- `.github/workflows/ci.yml`
- `.gitlab-ci.yml`

Pipeline menjalankan test backend, build frontend, dan validasi Docker Compose.

---

## 🐳 Docker

Proyek menyediakan `Dockerfile` dan `docker-compose.yml` untuk menjalankan service PHP-FPM, Nginx, PostgreSQL, dan Redis.

```bash
docker compose up -d --build
```

Port yang digunakan:

| Service | Port |
| --- | --- |
| Nginx | `8080:80` |
| PostgreSQL | `5432:5432` |
| Redis | `6379:6379` |
| PHP-FPM | `9000` |

Catatan: `docker-compose.yml` mengacu ke folder konfigurasi Nginx `./docker-compose/nginx/conf.d/`. Stack juga menyediakan worker queue dan scheduler agar proses notifikasi/background job berjalan.

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
