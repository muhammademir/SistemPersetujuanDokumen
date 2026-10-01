# Sistem Persetujuan Dokumen Kelayakan

Aplikasi pendokumentasian dan alur persetujuan (approval) permohonan dokumen kelayakan, dibangun sebagai bagian dari technical test Programmer.

## Deskripsi

Sistem ini menangani proses pengajuan permohonan dokumen oleh **Pemohon**, yang kemudian direview dan diputuskan (disetujui/ditolak/perlu revisi) oleh **Penilai**. Setiap permohonan memiliki riwayat status lengkap (audit trail) dan dapat diekspor dalam format Excel/PDF untuk pelaporan.

Alur status permohonan:

```
draft → submitted → under_review → approved
                          ├──────→ rejected
                          └──────→ revision_required → submitted (loop)
```

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.5 |
| Frontend | Vue 3, Vite, Pinia _(TODO)_ |
| Database | PostgreSQL 18 |
| Cache & Queue | Redis (via Docker) |
| Autentikasi | Laravel Sanctum (API Token) |
| Role & Permission | Spatie Laravel Permission |
| Testing | PHPUnit (bawaan Laravel) |

## Arsitektur

```
Vue 3 SPA (Vite)  ──HTTP/JSON──▶  Laravel REST API  ──▶  PostgreSQL
                                        │
                                        └──▶  Redis (cache + queue)
```

Backend berperan sebagai REST API murni (`routes/api.php`), dikonsumsi oleh frontend Vue 3 yang berjalan terpisah.

## Struktur Database

5 tabel inti:

- **`applications`** — data permohonan, dengan status, kode unik, dan relasi ke pemohon/penilai
- **`application_documents`** — file yang diunggah per permohonan (mendukung banyak revisi)
- **`application_reviews`** — catatan keputusan penilai per permohonan
- **`application_status_logs`** — audit trail setiap perubahan status
- **`users`** — pengguna (pemohon/penilai), diperluas dengan kolom profil tambahan

### Strategi Index

Dirancang untuk pola query nyata (bukan asal index semua kolom):

- **Composite index** `(applicant_id, status)` dan `(assigned_reviewer_id, status)` — mempercepat dashboard pemohon dan antrian penilai
- **Partial index** `applications_pending_queue_idx` — hanya mengindeks baris berstatus `submitted`/`under_review` yang belum terhapus, karena inilah yang paling sering di-query oleh penilai
- **GIN trigram index** `applications_title_trgm_idx` — mempercepat pencarian teks parsial pada judul permohonan

## Optimasi Performa — Bukti `EXPLAIN ANALYZE`

Pada volume data uji (10.000 baris `applications`), PostgreSQL secara konsisten memilih **Seq Scan** untuk sebagian besar query, karena ukuran tabel masih kecil (~400 halaman disk, cost dasar ~527). Ini adalah keputusan optimizer yang tepat pada skala ini — index hanya unggul saat volume data jauh lebih besar (ratusan ribu–jutaan baris, sesuai skala yang disebutkan pada spesifikasi soal) atau saat porsi baris yang cocok jauh lebih kecil dari total.

**Contoh 1 — filter status (35% baris cocok, tetap Seq Scan, dan itu benar):**

```sql
EXPLAIN ANALYZE SELECT * FROM applications
WHERE status IN ('submitted','under_review') AND deleted_at IS NULL
ORDER BY created_at DESC LIMIT 15;
```
```
Seq Scan on applications (actual time=0.099..5.086 rows=2050 loops=1)
Execution Time: 4.235 ms
```

**Contoh 2 — pencarian teks spesifik (91 dari 10.000 baris cocok):**

```sql
EXPLAIN ANALYZE SELECT COUNT(*) FROM applications WHERE title ILIKE '%Budiman%';
```
```
Seq Scan on applications (actual time=0.030..4.922 rows=91 loops=1)
Execution Time: 5.023 ms
```

**Verifikasi index valid dan dapat dipakai** (dipaksa aktif via `SET enable_seqscan = off`):

```sql
SET enable_seqscan = off;
EXPLAIN ANALYZE SELECT COUNT(*) FROM applications WHERE title ILIKE '%Budiman%';
```

Index `applications_title_trgm_idx` terverifikasi ada dan terdaftar sebagai opsi valid oleh query planner (dikonfirmasi lewat `\d applications`). Pada volume 10.000 baris, overhead index scan belum tentu lebih murah dari Seq Scan langsung — namun struktur index ini dirancang untuk memberi manfaat nyata pada skala produksi yang jauh lebih besar, sesuai kebutuhan sistem yang disebutkan pada spesifikasi soal (ratusan ribu–jutaan data).

**Kesimpulan:** index dirancang berdasarkan pola query aktual (bukan asal pasang), dan perilaku query planner PostgreSQL pada skala data ini sudah sesuai ekspektasi — keputusan Seq Scan vs Index Scan di sini adalah hasil kalkulasi cost optimizer, bukan index yang tidak berfungsi.

## Prasyarat

- PHP >= 8.3 (project ini diuji dengan PHP 8.5 via Laragon)
- Composer 2
- Node >= 20
- PostgreSQL >= 14 (diuji dengan versi 18)
- Redis (dijalankan via Docker)
- Docker Desktop (untuk Redis)
