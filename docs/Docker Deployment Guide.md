# 🐳 Panduan Deploy Docker SIPERDOK

Dokumen ini menjelaskan cara menjalankan SIPERDOK menggunakan Docker Compose, mulai dari install awal, update aplikasi, backup, troubleshooting, sampai menghapus stack.

---

## 📌 Ringkasan Stack

Docker Compose menjalankan service berikut:

| Service | Fungsi | Port |
| --- | --- | --- |
| `webserver` | Nginx untuk akses aplikasi | `8080:80` |
| `app` | PHP-FPM Laravel | `9000` internal |
| `migrate` | Menjalankan migration sekali sebelum app aktif | internal |
| `queue` | Worker Laravel Queue untuk upload dokumen/notifikasi | internal |
| `scheduler` | Laravel scheduler setiap 60 detik | internal |
| `postgres` | Database PostgreSQL | `5432:5432` |
| `redis` | Redis | `6379:6379` |

URL aplikasi default:

```bash
http://127.0.0.1:8080
```

---

## ✅ Prasyarat

Pastikan sudah tersedia:

- 🐳 Docker Desktop atau Docker Engine.
- 🧩 Docker Compose v2.
- 🟢 Node.js dan npm di host untuk build asset Vue.
- 🔧 Git.

Cek versi:

```bash
docker --version
docker compose version
node --version
npm --version
git --version
```

---

## 📥 Install Awal

Clone repository:

```bash
git clone https://github.com/hndko/app_siperdok_laravel12.git
cd app_siperdok_laravel12
```

Salin file environment:

```bash
cp .env.example .env
```

Untuk Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Sesuaikan `.env` untuk Docker:

```env
APP_NAME=SIPERDOK
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=db_siperdok_laravel12
DB_USERNAME=postgres
DB_PASSWORD=postgres

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

REDIS_HOST=redis
```

Jalankan build dan container:

```bash
docker compose up -d --build
```

Install dependency PHP melalui container:

```bash
docker compose exec app composer install
```

Generate APP_KEY:

```bash
docker compose exec app php artisan key:generate
```

Build asset Vue di host:

```bash
npm ci
npm run build
```

Jalankan seeder data awal:

```bash
docker compose exec app php artisan db:seed --force
```

Buat symbolic link storage:

```bash
docker compose exec app php artisan storage:link
```

Cek status container:

```bash
docker compose ps
```

Buka aplikasi:

```bash
http://127.0.0.1:8080
```

---

## 🚀 Menjalankan Aplikasi Harian

Start container:

```bash
docker compose up -d
```

Stop container tanpa menghapus data:

```bash
docker compose stop
```

Restart container:

```bash
docker compose restart
```

Lihat status:

```bash
docker compose ps
```

Lihat log semua service:

```bash
docker compose logs -f
```

Lihat log service tertentu:

```bash
docker compose logs -f app
docker compose logs -f webserver
docker compose logs -f queue
docker compose logs -f postgres
```

---

## 🔄 Update Aplikasi

Tarik update terbaru:

```bash
git pull origin main
```

Rebuild container:

```bash
docker compose up -d --build
```

Update dependency PHP jika `composer.json` atau `composer.lock` berubah:

```bash
docker compose exec app composer install
```

Update dependency frontend jika `package.json` atau `package-lock.json` berubah:

```bash
npm ci
npm run build
```

Jalankan migration:

```bash
docker compose exec app php artisan migrate --force
```

Bersihkan cache Laravel:

```bash
docker compose exec app php artisan optimize:clear
```

Restart worker queue agar memakai kode terbaru:

```bash
docker compose restart queue scheduler
```

Cek aplikasi:

```bash
docker compose ps
docker compose logs --tail=100 app queue scheduler
```

---

## 🧵 Queue dan Scheduler

Cek log worker queue:

```bash
docker compose logs -f queue
```

Restart queue jika ada perubahan kode job:

```bash
docker compose restart queue
```

Jalankan queue manual satu kali:

```bash
docker compose exec app php artisan queue:work database --once --tries=3
```

Cek failed jobs:

```bash
docker compose exec app php artisan queue:failed
```

Retry failed jobs:

```bash
docker compose exec app php artisan queue:retry all
```

Cek log scheduler:

```bash
docker compose logs -f scheduler
```

---

## 🗄️ Backup dan Restore Database

Buat folder backup:

```bash
mkdir -p backups
```

Untuk Windows PowerShell:

```powershell
New-Item -ItemType Directory -Force -Path backups
```

Backup PostgreSQL:

```bash
docker compose exec -T postgres pg_dump -U postgres db_siperdok_laravel12 > backups/siperdok_backup.sql
```

Restore PostgreSQL:

```bash
docker compose exec -T postgres psql -U postgres db_siperdok_laravel12 < backups/siperdok_backup.sql
```

Backup volume Docker PostgreSQL secara manual sebelum operasi besar:

```bash
docker compose stop
docker run --rm -v app_siperdok_laravel12_pgdata:/volume -v "%cd%/backups:/backup" alpine tar czf /backup/pgdata.tar.gz -C /volume .
docker compose up -d
```

Untuk Linux/macOS, gunakan:

```bash
docker compose stop
docker run --rm -v app_siperdok_laravel12_pgdata:/volume -v "$(pwd)/backups:/backup" alpine tar czf /backup/pgdata.tar.gz -C /volume .
docker compose up -d
```

---

## 🧹 Cache dan Maintenance

Bersihkan cache Laravel:

```bash
docker compose exec app php artisan optimize:clear
```

Cache konfigurasi untuk production:

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Aktifkan maintenance mode:

```bash
docker compose exec app php artisan down
```

Nonaktifkan maintenance mode:

```bash
docker compose exec app php artisan up
```

---

## 🧪 Validasi Setelah Deploy

Cek route Laravel:

```bash
docker compose exec app php artisan route:list
```

Cek migration:

```bash
docker compose exec app php artisan migrate:status
```

Cek aplikasi dari terminal:

```bash
curl -I http://127.0.0.1:8080
```

Untuk Windows PowerShell:

```powershell
Invoke-WebRequest -Uri http://127.0.0.1:8080 -UseBasicParsing
```

Jalankan test:

```bash
docker compose exec app php artisan test
```

---

## 🧯 Troubleshooting

Jika container tidak start:

```bash
docker compose ps -a
docker compose logs --tail=100 app webserver postgres queue scheduler
```

Jika muncul error `pull access denied for siperdok-app`:

```bash
docker compose build app queue scheduler migrate
docker compose up -d
```

Jika halaman timeout atau error tabel `cache`/`sessions` tidak ada:

```bash
docker compose exec app php artisan migrate --force
docker compose restart app webserver queue scheduler
```

Jika upload file tidak bisa diakses:

```bash
docker compose exec app php artisan storage:link
docker compose exec app chmod -R 775 storage bootstrap/cache
```

Jika frontend lama masih tampil:

```bash
npm run build
docker compose restart webserver
```

Jika queue tidak memproses upload/notifikasi:

```bash
docker compose logs --tail=100 queue
docker compose restart queue
docker compose exec app php artisan queue:failed
```

---

## 🗑️ Menghapus Stack

Stop dan hapus container tanpa menghapus database volume:

```bash
docker compose down
```

Hapus container beserta image lokal yang dibuat Compose:

```bash
docker compose down --rmi local
```

Hapus container, network, dan volume database. Perintah ini akan menghapus data PostgreSQL:

```bash
docker compose down -v
```

Hapus image `siperdok-app`:

```bash
docker image rm siperdok-app
```

Hapus file dependency lokal jika ingin install ulang bersih:

```bash
rm -rf vendor node_modules public/build
```

Untuk Windows PowerShell:

```powershell
Remove-Item -Recurse -Force vendor,node_modules,public/build
```

---

## 📌 Urutan Cepat

Install awal:

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
npm ci
npm run build
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan storage:link
```

Update:

```bash
git pull origin main
docker compose up -d --build
docker compose exec app composer install
npm ci
npm run build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose restart queue scheduler
```

Hapus penuh:

```bash
docker compose down -v --rmi local
docker image rm siperdok-app
```
