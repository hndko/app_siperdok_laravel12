# 📡 Dokumentasi REST API SIPERDOK

Base URL: `/api/v1`  
Authentication: Bearer Token Laravel Sanctum  
Content-Type: `application/json`, kecuali upload menggunakan `multipart/form-data`  
Timezone aplikasi: `Asia/Jakarta` pada environment lokal  
Format error umum: `status`, `message`, dan `errors` untuk validasi `422`.

## 🔐 Authentication

| Method | Endpoint | Auth | Role | Keterangan |
| --- | --- | --- | --- | --- |
| `POST` | `/register` | Tidak | Publik | Registrasi pemohon baru. |
| `POST` | `/login` | Tidak | Publik | Login dan mendapatkan token Sanctum. |
| `GET` | `/me` | Ya | Semua | Data user aktif, role, dan notifikasi ringkas. |
| `PUT` | `/profile` | Ya | Semua | Update profil dan optional password. |
| `POST` | `/profile` | Ya | Semua | Alias update profil untuk form multipart. |
| `POST` | `/logout` | Ya | Semua | Hapus token aktif. |

Contoh login:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"pemohon@example.com","password":"password"}'
```

## 📁 Projects

| Method | Endpoint | Auth | Role | Keterangan |
| --- | --- | --- | --- | --- |
| `GET` | `/projects` | Ya | Semua sesuai policy | Daftar permohonan dengan cursor pagination. |
| `POST` | `/projects` | Ya | Pemohon/Admin | Buat draft atau langsung submit permohonan. |
| `GET` | `/projects/{id}` | Ya | Pemilik/Admin/Penilai | Detail permohonan. |
| `PUT`/`POST` | `/projects/{id}` | Ya | Pemilik draft/revisi | Update dan resubmit permohonan. |
| `DELETE` | `/projects/{id}` | Ya | Pemilik/Admin | Hapus hanya jika aturan bisnis mengizinkan. |

Query daftar project:

| Parameter | Tipe | Keterangan |
| --- | --- | --- |
| `search` | string | Cari nomor, judul, nama pemohon, atau perusahaan. |
| `status` | enum | `draft`, `submitted`, `in_review`, `revision`, `approved`, `rejected`, `certificate_issued`. |
| `document_type_id` | integer | Filter jenis dokumen. |
| `applicant_id` | integer | Filter pemohon. |
| `evaluator_id` | integer | Filter penilai. |
| `date_from` / `date_to` | date | Filter tanggal dibuat. |
| `per_page` | integer | 1-100, default 15. |

Contoh upload:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/projects \
  -H "Authorization: Bearer <TOKEN>" \
  -F "title=Pembangunan Gedung Baru" \
  -F "document_type_id=1" \
  -F "description=Pengajuan dokumen kelayakan." \
  -F "submit_action=submit" \
  -F "document=@/path/to/file.pdf"
```

## ⚖️ Assessments

| Method | Endpoint | Auth | Role | Keterangan |
| --- | --- | --- | --- | --- |
| `POST` | `/assessments/{id}/start-review` | Ya | Admin/Penilai | Ubah `submitted` menjadi `in_review`. |
| `POST` | `/assessments/{id}` | Ya | Admin/Penilai bertugas | Keputusan `approved`, `revision`, atau `rejected`. |
| `GET` | `/assessments/history` | Ya | Semua sesuai visibility | Cursor pagination histori penilaian. |

Keputusan final wajib didahului checklist administrasi yang selesai.

## ✅ Verification Checklist

| Method | Endpoint | Auth | Role | Keterangan |
| --- | --- | --- | --- | --- |
| `GET` | `/projects/{id}/verification-checklists` | Ya | Pemilik/Admin/Penilai | Ambil checklist dan ringkasan progress. |
| `PUT` | `/projects/{id}/verification-checklists` | Ya | Admin/Penilai | Simpan checklist saat status `in_review`. |

Status item: `pending`, `passed`, `failed`, `not_applicable`.

## 🔔 Notifications

| Method | Endpoint | Auth | Role | Keterangan |
| --- | --- | --- | --- | --- |
| `GET` | `/notifications` | Ya | Semua | Cursor pagination notifikasi milik user. |
| `PATCH` | `/notifications/{notification}/read` | Ya | Pemilik | Tandai satu notifikasi dibaca. |
| `PATCH` | `/notifications/read-all` | Ya | Semua | Tandai seluruh notifikasi user dibaca. |

## 📊 Dashboard dan Master Data

| Method | Endpoint | Auth | Role | Keterangan |
| --- | --- | --- | --- | --- |
| `GET` | `/dashboard` | Ya | Semua | Statistik sesuai visibility role. |
| `GET` | `/document-types` | Ya | Semua | Master jenis dokumen aktif, cache 10 menit. |
| `GET` | `/users` | Ya | Admin | Daftar user dengan cursor pagination. |

## 📤 Export dan Certificate

| Method | Endpoint | Auth | Role | Keterangan |
| --- | --- | --- | --- | --- |
| `GET` | `/exports/projects/csv` | Ya | Admin/Penilai | Export CSV streaming. |
| `GET` | `/exports/projects/xlsx` | Ya | Admin/Penilai | Export Excel dari query. |
| `GET` | `/exports/projects/{id}/certificate` | Ya | Pemilik/Admin/Penilai | PDF resmi hanya untuk `certificate_issued`. |
| `POST` | `/projects/{id}/issue-certificate` | Ya | Admin/Penilai | Terbitkan certificate dari status `approved`. |
| `GET` | `/certificates/verify/{certificateNumber}` | Tidak | Publik | Verifikasi nomor certificate, rate limited. |

## 📄 Pagination

Endpoint daftar utama menggunakan cursor pagination untuk dataset besar.

Contoh response ringkas:

```json
{
  "data": [],
  "links": {
    "next": "http://127.0.0.1:8000/api/v1/projects?cursor=...",
    "prev": null
  },
  "meta": {
    "per_page": 15
  },
  "status": "success",
  "message": "OK"
}
```

## ⚠️ Error Response

Validasi:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["Judul wajib diisi."]
  }
}
```

Forbidden:

```json
{
  "message": "This action is unauthorized."
}
```

Status umum: `200`, `201`, `401`, `403`, `404`, `422`, `429`, `500`.
