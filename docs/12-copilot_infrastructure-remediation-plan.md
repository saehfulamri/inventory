# Rencana Perbaikan Infrastruktur

Roadmap ini mengurutkan perbaikan berdasarkan risiko produksi dan dependency
antarpekerjaan. Status merujuk kondisi terakhir saat dokumen ini dibuat;
pekerjaan yang sudah diimplementasikan tetap perlu masuk ke `main` melalui
GitHub Flow sebelum dianggap aktif di repository utama.

## Ringkasan Status

| Fase | Area | Status | Dependency |
| --- | --- | --- | --- |
| 1 | Backup, HTTPS, dan supply chain Actions | Selesai | — |
| 2 | Deployment release atomik | Selesai | Fase 1 |
| 3 | Quality gates CI | Diimplementasikan; menunggu verifikasi CI MySQL dan merge | Fase 1 |
| 4 | Operasional queue produksi | Belum dikerjakan | Fase 2 |
| 5 | Observability produksi | Belum dikerjakan | Fase 2 |
| 6 | Validasi end-to-end | Belum dikerjakan | Fase 3, 4, dan 5 |

## Fase 1 — Mengurangi Risiko Kehilangan Data dan Akses Tidak Aman

**Risiko:** kritis; paparan secret, backup tak dapat dipulihkan, atau traffic
produksi tanpa perlindungan HTTPS.

**Status:** selesai.

- Enkripsi backup database dan `.env`; simpan kunci terpisah dari backup dan
  host aplikasi.
- Hindari password database di argument command; gunakan credential file
  sementara berpermission ketat.
- Simpan backup terenkripsi di lokasi off-site, gunakan checksum, retention,
  dan jadwalkan restore drill.
- Redirect HTTP ke HTTPS dan aktifkan HSTS serta security headers pada contoh
  konfigurasi web server.
- Pin GitHub Actions ke commit SHA immutable.

**Kriteria selesai:** prosedur backup/restore dan TLS terdokumentasi; workflow
CI memakai action SHA tetap; tidak ada secret baru di repository.

## Fase 2 — Deployment Release Aman dan Rollback

**Risiko:** tinggi; deployment gagal dapat menyebabkan downtime atau aplikasi
berada pada versi campuran.

**Status:** selesai; implementasi tersedia pada commit `93dbb74`.

**Dependency:** Fase 1.

- Bangun release di direktori versi baru dengan shared `.env` dan storage.
- Jalankan install dependency, build asset, cache, dan preflight sebelum
  aktivasi.
- Jalankan migrasi backward-compatible dalam maintenance window.
- Ganti symlink `current` secara atomik dan verifikasi health endpoint.
- Sediakan rollback ke release sebelumnya tanpa menjalankan
  `migrate:rollback` otomatis; pertahankan minimal lima release.
- Deploy hanya dari tag/commit yang berasal dari `main` setelah PR di-merge.

**Kriteria selesai:** script deploy dan rollback tersedia; release lama tetap
utuh sampai release baru disiapkan; prosedur deploy, health check, serta
rollback terdokumentasi.

## Fase 3 — Memperluas Quality Gates CI

**Risiko:** sedang-tinggi; bug dapat lolos karena test hanya berjalan di SQLite
atau dependency/action berubah tanpa terdeteksi.

**Status:** workflow sudah diimplementasikan di branch
`chore/ci-quality-gates`; menunggu job MySQL di GitHub Actions lulus dan
perubahan di-merge ke `main`.

**Dependency:** Fase 1 untuk action immutable.

- Validasi metadata Composer dan sintaks seluruh file PHP.
- Jalankan Pint, test suite SQLite, dan build frontend sebagai gate PR.
- Tambahkan audit Composer dan npm pada pull request.
- Tambahkan job MySQL 8.0 yang menjalankan migrasi bersih dan seluruh test suite.
- Jalankan audit dependency terjadwal mingguan serta manual.
- Jangan menambah PHPStan/Psalm atau dependency lain tanpa persetujuan.

**Kriteria selesai:** seluruh required checks—PHP 8.4, PHP 8.5, dan MySQL
8.0—berhasil pada PR; audit dependency tidak menemukan advisory High/Critical;
workflow terjadwal dapat dijalankan manual.

## Fase 4 — Menetapkan Operasi Queue Produksi

**Risiko:** sedang; jika aplikasi mulai mengirim job asynchronous tanpa worker,
job akan menumpuk dan pekerjaan tidak selesai.

**Status:** belum dikerjakan.

**Dependency:** Fase 2.

- Tinjau apakah queue database saat ini masih sesuai atau Redis diperlukan.
- Jika queue digunakan, jalankan worker melalui Supervisor atau systemd dengan
  restart saat deploy.
- Tetapkan timeout, retry policy, `retry_after`, dan perilaku setelah commit
  transaksi.
- Pantau `failed_jobs` dan sediakan prosedur retry/triage.
- Jika aplikasi tetap sinkron tanpa job, dokumentasikan keputusan dan kriteria
  kapan worker perlu diaktifkan.

**Kriteria selesai:** pilihan backend dan konfigurasi worker terdokumentasi;
worker dapat pulih setelah restart/deploy; failed job dapat dideteksi dan
ditangani.

## Fase 5 — Menambahkan Observability Produksi

**Risiko:** sedang; gangguan dapat berlangsung lama karena error, backup gagal,
atau layanan tidak sehat tidak terdeteksi.

**Status:** belum dikerjakan.

**Dependency:** Fase 2.

- Tentukan tujuan log terpusat (atau stderr/syslog) dan kebijakan retensinya.
- Pantau health endpoint `/up`, uptime, error rate, dan kapasitas disk.
- Tambahkan alert untuk exception penting, backup/upload gagal, dan failed
  queue jobs bila queue digunakan.
- Tetapkan siapa yang menerima alert, tingkat urgensi, dan langkah respons.
- Hindari mengirim credential, token, atau data sensitif ke log.

**Kriteria selesai:** sinyal kesehatan aplikasi dan kegagalan operasional
terpantau; alert memiliki penerima dan tindakan respons yang jelas.

## Fase 6 — Validasi End-to-End dan Go-Live

**Risiko:** tahap penutupan; memastikan seluruh kontrol berjalan bersama di
lingkungan yang representatif.

**Status:** belum dikerjakan.

**Dependency:** Fase 3, Fase 4, dan Fase 5.

- Jalankan test suite, Pint, PHP lint/static checks yang disetujui, dan build
  frontend.
- Jalankan migration test dan test suite terhadap MySQL.
- Lakukan smoke test deployment, health check, maintenance mode, dan rollback.
- Verifikasi log/alert serta pemrosesan queue jika queue digunakan.
- Jalankan backup/restore drill pada staging clone dan cocokkan data penting.
- Tinjau checklist keamanan, branch protection, RPO/RTO, dan kesiapan operasi.

**Kriteria selesai:** semua required CI checks lulus; deployment dan rollback
teruji; backup berhasil dipulihkan; monitoring dan prosedur incident response
siap digunakan.

## Urutan Pelaksanaan

```text
Fase 1 ──> Fase 2 ──> Fase 4 ──┐
   └─────> Fase 3 ──────────────┼──> Fase 6
Fase 2 ──> Fase 5 ──────────────┘
```

Kerjakan Fase 4 dan 5 secara paralel setelah Fase 2. Fase 6 baru dimulai
setelah Fase 3, 4, dan 5 selesai. Setiap perubahan masuk melalui branch
terpisah dan Pull Request ke `main`; deployment produksi hanya berasal dari
tag rilis yang dibuat setelah merge.
