# UI/UX Audit Report – Sistem Inventori & Penjualan (Laravel)

## 1. Ringkasan Temuan
- **Konsistensi Design Tokens**: Dokumen desain (`09-design.md`) mendefinisikan token warna, tipografi, radius, dan spasi. Implementasi saat ini hanya menggunakan Tailwind default (warna `primary` #2563eb, `danger` #dc2626) dan tidak memuat token‑token khusus Apple‑style yang didefinisikan dalam `09‑design.md`.
- **Komponen UI**: Folder `resources/js/Components/ui` berisi komponen UI generik (VButton, VCard, VTable, dll). Tidak ada komponen yang menyatu dengan token desain (`primary`, `rounded.pill`, dsb.) dan tidak ada implementasi `VButton` yang mencerminkan gaya **button‑primary** berwarna biru aksi, radius pill, atau efek `transform: scale(0.95)`.
- **CSS Tokenisasi**: `resources/css/app.css` hanya meng‑import Tailwind dan mendefinisikan font‑sans. Tidak ada custom properties untuk warna, radius, spacing, atau tipografi yang disebutkan di `09‑design.md`.
- **Responsive Breakpoints**: Tailwind default breakpoint (sm, md, lg, xl, 2xl) tidak cocok dengan breakpoint yang didefinisikan di design doc (mis. 419 px, 640 px, 833 px, 1068 px, 1440 px). Tidak ada media query khusus untuk menyesuaikan layout sesuai panduan.
- **Aksesibilitas**: Tidak ada inspeksi otomatis, namun komponen UI belum menambahkan `aria-label`, `role`, atau fokus visual yang mengikuti desain (outline `2px solid primary‑focus`).
- **Shadow & Elevation**: Design menekankan **satu drop‑shadow** hanya pada gambar produk. Tailwind `shadow` utility tidak digunakan secara selektif; tidak ada aturan yang membatasi shadow pada elemen gambar.
- **Dark Mode**: Tidak ada konfigurasi dark‑mode di Tailwind atau CSS. Dokumen desain mencakup dark‑surface token (`surface‑black`, `primary‑on‑dark`) namun tidak terimplementasi.
- **Form Validation & Error States**: Dokumentasi `09‑design.md` mencatat bahwa state validasi belum didokumentasikan. Di UI, komponen form (`VFormField.vue`) belum memiliki styling khusus untuk error/validasi.
- **Penggunaan Tailwind**: Class‑name utilitas tersebar di Blade/Vue files, namun tidak konsisten dengan token desain. Mis. `bg-blue-600` dipakai alih‑alih `bg-primary` yang seharusnya mengacu ke token warna desain.

## 2. Kesesuaian dengan UI/UX Best Practices
| Kriteria | Status | Catatan |
|---|---|---|
| **Design Tokens (warna, tipografi, radius, spacing)** | ❌ Tidak ada token khusus, hanya Tailwind default | Perlu menambahkan custom properties & extend Tailwind theme.
| **Button Primary** | ❌ Tidak ada komponen yang mengikuti spec (`primary` biru, pill, active scale) | Implementasi VButton harus di‑extend atau buat `VButtonPrimary.vue`.
| **Shadow Policy** | ❌ Shadow dapat diterapkan bebas | Batasi shadow hanya pada elemen `<img>` produk.
| **Responsive Breakpoints** | ❌ Menggunakan breakpoint Tailwind default | Tambahkan custom screens di `tailwind.config.js` sesuai tabel design.
| **Accessibility (focus ring, aria)** | ⚠️ Belum diverifikasi | Pastikan semua interactive element memiliki outline `2px solid primary‑focus` dan `aria-label`.
| **Dark‑mode tokens** | ❌ Tidak ada support | Tambahkan `dark:` variant dan CSS custom properties.
| **Form Validation states** | ⚠️ Tidak terdokumentasi | Tambahkan style error (`border-danger`, `text-danger`) dan visual feedback.
| **Component Reusability** | ✅ Komponen UI ada, tetapi tidak mematuhi design system | Refactor agar setiap komponen mengacu token.
| **Performance (Purged CSS)** | ✅ Tailwind purge default | Tetap optimal.

## 3. Rekomendasi Perbaikan (Prioritas Tinggi → Rendah)
1. **Definisikan Design Tokens di Tailwind**
   - Tambahkan `extend` pada `tailwind.config.js` untuk warna, font‑family, radius, spacing sesuai `09‑design.md`.
   - Export custom CSS variables (`--color-primary`, `--radius-pill`, dsb.) di `resources/css/app.css` untuk dipakai di Vue components.
2. **Buat Komponen UI yang Mengikuti Spec**
   - `VButtonPrimary.vue` (atau extend `VButton.vue`) dengan class: `bg-primary text-on-primary rounded-pill py-2.5 px-5 hover:scale-95 focus:outline-none focus:ring-2 focus:ring-primary-focus`.
   - Implementasikan `transform: scale(0.95)` pada state `active`.
3. **Implementasi Shadow Kebijakan**
   - Tambahkan CSS selector: `.product-image { @apply shadow-none; box-shadow: 0 5px 30px rgba(0,0,0,0.22); }`
   - Pastikan tidak ada `shadow` pada card atau button.
4. **Custom Breakpoints**
   - Di `tailwind.config.js` tambahkan:
     ```js
     screens: {
       'sm-phone': {'max': '419px'},
       'phone': {'min': '420px', 'max': '640px'},
       'tablet-portrait': {'min': '641px', 'max': '833px'},
       'tablet-landscape': {'min': '834px', 'max': '1068px'},
       'desktop': {'min': '1069px', 'max': '1440px'},
       'wide': {'min': '1441px'},
     }
     ```
   - Update component markup agar menggunakan breakpoint baru.
5. **Dark‑Mode Support**
   - Aktifkan `darkMode: 'class'` di Tailwind config.
   - Tambahkan CSS vars untuk dark surface (`--color-surface-black`, `--color-primary-on-dark`).
   - Pastikan layout meng‑switch kelas `dark` via root `<html class="dark">`.
6. **Aksesibilitas**
   - Tambahkan `focus-visible` styling pada semua interactive elemen.
   - Pastikan label form ter‑link dengan `for`/`id`.
   - Tambahkan `aria-live` pada flash messages.
7. **Form Validation UI**
   - Buat kelas `input-error` dengan `border-danger` dan `text-danger`.
   - Implementasikan dalam `VFormField.vue` serta tampilkan pesan error.
8. **Dokumentasi & Testing**
   - Tambahkan unit/feature test untuk komponen UI baru (contoh: `VButtonPrimaryTest`).
   - Update `09‑design.md` dengan contoh class Tailwind yang di‑generate.

## 4. Langkah Selanjutnya
- **Sprint 1**: Implementasi design tokens & primary button component. Commit perubahan di `tailwind.config.js`, `resources/css/app.css`, serta buat `resources/js/Components/ui/VButtonPrimary.vue`.
- **Sprint 2**: Tambahkan breakpoints, dark‑mode, dan shadow policy.
- **Sprint 3**: Perbaikan aksesibilitas & form validation UI.
- **Sprint 4**: Review visual di browser (`npm run dev`), pastikan semua halaman (Dashboard, Produk, Supplier, POS) mematuhi token dan komponen baru.
- **Sprint 5**: Tambahkan atau perbarui tes serta jalankan `vendor/bin/pint` untuk formatting.

*Catatan*: Semua perubahan harus mengikuti aturan Laravel Boost (gunakan `php artisan make:component`, `php artisan make:test`, dan jalankan `vendor/bin/pint --format agent` setelah commit).
