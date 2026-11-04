# Kebijakan Keamanan

Keamanan aplikasi ini penting. Kami menghargai upaya siapa pun yang melaporkan
kerentanan secara bertanggung jawab.

## Versi yang Didukung

Perbaikan keamanan diberikan untuk rilis terbaru pada major version yang sedang
aktif.

| Versi | Didukung |
| --- | --- |
| `0.3.x` (tag rilis terbaru) | ✅ |
| `< 0.3` | ❌ |

Proyek belum mencapai `1.0.0`; API dan skema dapat berubah antar minor version.

## Melaporkan Kerentanan

**Jangan membuka issue publik untuk masalah keamanan.**

Gunakan salah satu kanal privat berikut:

1. **GitHub Security Advisories** (disarankan):
   <https://github.com/saehfulamri/inventory/security/advisories/new>
2. Jika kanal tersebut tidak tersedia, hubungi maintainer
   [@saehfulamri](https://github.com/saehfulamri) melalui GitHub.

Sertakan sebanyak mungkin informasi berikut:

- Jenis kerentanan dan komponen yang terdampak (file/route).
- Langkah reproduksi atau proof-of-concept.
- Dampak yang mungkin terjadi dan kondisi yang diperlukan.
- Versi/commit yang Anda uji.
- Saran mitigasi (opsional).

## Proses Penanganan

- **Konfirmasi awal**: dalam 3 hari kerja.
- **Penilaian & perbaikan**: target 14 hari kerja untuk kerentanan kritis/tinggi;
  isu lain sesuai tingkat keparahan.
- **Koordinasi pengungkapan**: kami akan berkomunikasi dengan Anda sebelum
  detail dipublikasikan. Anda akan diberi kredit pada advisory, kecuali meminta
  sebaliknya.
- **Cakupan**: laporan mengenai kode di repositori ini. Masalah pada dependensi
  pihak ketiga sebaiknya dilaporkan ke proyek pemiliknya (kami tetap menerima
  informasi agar dapat memperbarui dependensi).

## Praktik Keamanan Proyek

- Validasi input melalui Form Request, otorisasi melalui Policy/Gate, dan
  escaping output (Vue/Blade) secara default.
- Semua mutasi stok memakai database transaction dan row lock.
- Header keamanan (CSP dengan nonce, `X-Frame-Options`, dsb.) dikirim global.
- Audit dependensi dijalankan di CI (`composer audit`, `npm audit`) dan
  Dependabot memantau pembaruan.
- Jangan pernah melaporkan atau menyimpan secret (`.env`, kredensial) di issue,
  PR, atau log.
