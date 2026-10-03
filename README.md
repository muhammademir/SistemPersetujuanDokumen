# Sistem Informasi Persetujuan Dokumen Kelayakan (SiPerDok)

Sistem Informasi berbasis web untuk mengelola dan memfasilitasi proses pengajuan, verifikasi administrasi, penilaian teknis, hingga penerbitan keputusan persetujuan dokumen kelayakan secara terpadu, transparan, dan efisien.

---

## 📌 Daftar Isi
- [Teknologi Stack](#-teknologi-stack)
- [Alur Bisnis Sistem](#-alur-bisnis-sistem)
- [Hak Akses Pengguna (Role User)](#-hak-akses-pengguna-role-user)
- [Fitur Utama](#-fitur-utama)
- [Arsitektur & Desain Database](#-arsitektur--desain-database)
- [Petunjuk Instalasi & Menjalankan Aplikasi](#-petunjuk-instalasi--menjalankan-aplikasi)
- [Akun Percobaan (Demo Accounts)](#-akun-percobaan-demo-accounts)
- [Pengujian (Testing)](#-pengujian-testing)
- [Dokumentasi API](#-dokumentasi-api)

---

## 🛠 Teknologi Stack

| Bagian | Teknologi / Library |
|---|---|
| **Backend** | PHP 8.2+, Laravel 11/12 (REST API) |
| **Frontend** | Vue 3 (Composition API, `<script setup>`), TypeScript, Vite |
| **UI Framework** | **PrimeVue 5** (Aura Theme Preset via `@primeuix/themes`), PrimeIcons, Tailwind CSS |
| **Database** | PostgreSQL (Normalized schema, partial index, GIN trigram index, constraints) |
| **State & Router** | Pinia, Vue Router 4 |
| **Visualisasi Data**| ApexCharts (`vue3-apexcharts`) |
| **Autentikasi** | Laravel Sanctum (Bearer Token / Cookie Stateful) & Spatie Permission |
| **Cache & Queue** | Redis / Database Cache & Asynchronous Jobs |
| **Version Control** | Git (Branching & commit bertahap terstruktur) |

---

## 🔄 Alur Bisnis Sistem

```
+----------------------------------------------------------------------------------------------------+
|                                              ALUR BISNIS                                            |
|                                SISTEM PERSETUJUAN DOKUMEN KELAYAKAN                                |
+----------------------------------------------------------------------------------------------------+

   [PEMOHON]                                                  [PENILAI / PENGUJI]
      │                                                              │
   ( START )                                                         │
      │                                                              │
      ▼                                                              │
[Mempersiapkan Dokumen & Data]                                       │
      │                                                              │
      ▼                                                              │
[Login & Masuk Sistem]                                               │
      │                                                              │
      ▼                                                              │
[Melengkapi Form Pengajuan Dokumen]                                  │
      │                                                              │
      ▼                                                              │
[SUBMIT / Kirim Permohonan] ─────────────────────────────────────────▶ [Menerima Notifikasi & Dokumen]
                                                                     │
                                                                     ▼
                                                               [Penilaian / Review Dokumen]
                                                                     │
                                                                     ▼
                                                               <Keputusan Penilaian?>
                                                                │     │       │
                                    ┌───────────────────────────┘     │       └───────────────────────┐
                                    │ (1. SETUJU)                     │ (2. REVISI)                   │ (3. DITOLAK)
                                    ▼                                 ▼                               ▼
                           [Pengesahan Dokumen]              [Notifikasi Revisi]             [Notifikasi Penolakan]
                                    │                                 │                               │
                                    ▼                                 ▼                               ▼
                        [Dokumen Terbit & Notifikasi]      [Perbaiki Dokumen / Update]     [Melakukan Pengajuan Baru]
                                    │                                 │                               │
                                    ▼                                 ▼                               └──────▶ (Ke START)
                                ( SELESAI )               [Submit Ulang Perbaikan] ───▶ (Ke Penilaian)
```

---

## 👥 Hak Akses Pengguna (Role User)

### 1. Pemohon Dokumen
- **Login & Register**: Mendaftar dan masuk ke akun pemohon.
- **Dashboard Pemohon**: Memantau statistik ringkasan permohonan (Total, Draft, Menunggu Verifikasi, Sedang Ditinjau, Perlu Revisi, Disetujui, Ditolak) beserta grafik visualisasi distribusi status.
- **Membuat Permohonan Baru**: Mengisi jenis dokumen (SLF, AMDAL, IMB, UKL-UPL, SIUP), judul, deskripsi, dan melampirkan berkas (PDF/DOC/XLS).
- **Simpan Draft**: Menyimpan permohonan berstatus `draft` untuk dilengkapi sewaktu-waktu.
- **Kirim Permohonan**: Mengubah status dari `draft` menjadi `submitted` untuk dinilai oleh penilai.
- **Perbaiki Revisi**: Jika permohonan berstatus `revision_required`, pemohon dapat membaca catatan penilai, memperbarui berkas/data, dan mengirim ulang (`resubmit`).
- **Melihat Detail & Riwayat**: Membuka audit trail/timeline status lengkap dari pengajuan sampai akhir.

### 2. Penilai Dokumen
- **Login**: Masuk ke panel khusus penilai.
- **Dashboard Penilai**: Melihat statistik beban antrean, grafik beban status permohonan, dan ringkasan antrean prioritas.
- **Antrean Penilaian**: Daftar permohonan yang berstatus `submitted` (Menunggu Verifikasi) atau `under_review` (Sedang Ditinjau).
- **Verifikasi & Keputusan**:
  - **Setuju (`approved`)**: Memberikan persetujuan permohonan.
  - **Perlu Revisi (`revision_required`)**: Meminta pemohon memperbaiki dokumen disertai catatan wajib.
  - **Ditolak (`rejected`)**: Menolak dokumen dengan alasan penilaian.
- **Riwayat Penilaian**: Memantau seluruh jejak rekam penilaian yang pernah diputuskan.

---

## ⚡ Petunjuk Instalasi & Menjalankan Aplikasi

### Prasyarat Sistem
- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18 (disarankan Node 20+) & npm
- PostgreSQL >= 14
- Redis (opsional, jika menggunakan Redis cache/queue)

### 1. Setup Backend (Laravel)

```bash
cd backend

# 1. Salin konfigurasi environment
cp .env.example .env

# 2. Install dependensi PHP
composer install

# 3. Generate Application Key
php artisan key:generate

# 4. Konfigurasi database PostgreSQL pada file .env:
# DB_CONNECTION=pgsql
# DB_HOST=localhost
# DB_PORT=5432
# DB_DATABASE=perizinan_db
# DB_USERNAME=postgres
# DB_PASSWORD=your_password

# 5. Jalankan migrasi dan seeder
php artisan migrate:fresh --seed

# 6. Jalankan storage link (untuk berkas upload)
php artisan storage:link

# 7. Jalankan server Laravel API
php artisan serve
# Server akan aktif di http://localhost:8000
```

### 2. Setup Frontend (Vue 3 + PrimeVue)

```bash
cd frontend

# 1. Install dependensi Node.js
npm install

# 2. Jalankan development server
npm run dev
# Frontend aktif di http://localhost:5173

# 3. Build untuk produksi (opsional untuk verifikasi build)
npm run build
```

---

## 🔑 Akun Percobaan (Demo Accounts)

Tersedia tombol **Akun Demo Cepat** pada halaman login:

| Peran (Role) | Email | Password |
|---|---|---|
| **Pemohon Dokumen** | `pemohon@demo.test` | `password` |
| **Penilai Dokumen** | `penilai@demo.test` | `password` |

---

## 🧪 Pengujian (Testing)

Jalankan rangkaian tes backend menggunakan PHPUnit:

```bash
cd backend
php artisan test
```

Verifikasi build frontend:

```bash
cd frontend
npm run build
```

---

## 📄 Dokumentasi API

REST API backend dapat diakses pada prefix `/api`:

| Method | Endpoint | Deskripsi | Hak Akses |
|---|---|---|---|
| `POST` | `/api/login` | Autentikasi pengguna | Publik |
| `POST` | `/api/register` | Pendaftaran pengguna baru | Publik |
| `GET` | `/api/me` | Profil pengguna login | Authenticated |
| `POST` | `/api/logout` | Revoke token autentikasi | Authenticated |
| `GET` | `/api/dashboard/summary` | Statistik agregasi dashboard | Authenticated |
| `GET` | `/api/applications` | Daftar permohonan dokumen (Filter & Search) | Authenticated |
| `POST` | `/api/applications` | Membuat permohonan baru | Pemohon |
| `GET` | `/api/applications/{id}` | Detail permohonan, berkas, review & log | Authenticated |
| `PUT` | `/api/applications/{id}` | Memperbarui permohonan (Draft/Revisi) | Pemohon |
| `POST` | `/api/applications/{id}/submit` | Mengajukan permohonan untuk dinilai | Pemohon |
| `POST` | `/api/applications/{id}/documents` | Mengunggah berkas lampiran | Pemohon |
| `POST` | `/api/applications/{id}/review` | Memberikan keputusan penilaian (Setuju/Revisi/Tolak) | Penilai |
| `GET` | `/api/reviews/history` | Riwayat penilaian yang dilakukan | Penilai |

---

## 🌿 Struktur Branch Git

- `main` / `master`: Branch rilis produksi stabil.
- `develop`: Branch integrasi pengembangan.
- `feature/database-schema`: Desain database PostgreSQL, relasi, index, dan constraint.
- `feature/auth-sanctum`: Autentikasi Laravel Sanctum dan role/permission.
- `feature/application-crud`: Workflow CRUD, submission, dan status audit log.
- `feature/document-upload`: Validasi berkas lampiran dan upload handler.
- `feature/frontend-setup`: Setup awal Vue 3, Pinia, dan Tailwind CSS.
- `feature/frontend-primevue`: Migrasi menyeluruh tampilan antarmuka ke **PrimeVue 5** (Aura preset, Tag, Card, DataTable, Dialog, Timeline, Button, Select, Message, dll).
