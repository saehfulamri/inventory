## Deskripsi

Jelaskan **apa**, **mengapa**, dan **bagaimana** perubahan ini.

Tautkan issue/backlog task bila ada: `TASK-063`, `#12`, `fixes #12`.

## Jenis perubahan

- [ ] Bug fix
- [ ] Fitur baru
- [ ] Perbaikan dokumentasi
- [ ] Refactor / peningkatan internal
- [ ] Test

## Checklist

- [ ] `composer test` lulus (atau minimal test relevan)
- [ ] `vendor/bin/pint --test` lulus
- [ ] Validation (Form Request) ditambahkan bila ada input baru
- [ ] Authorization (Policy/Gate) konsisten bila ada halaman/menu baru
- [ ] Perubahan stok/transaksi memakai Service + transaction + stock movement
- [ ] Tidak ada perubahan file yang tidak relevan
- [ ] README / `08-changelog.md` diperbarui bila diperlukan

## Screenshot (opsional)

Tambahkan tangkapan layar bila perubahan menyentuh UI.

## Catatan untuk reviewer

_Jelaskan bagian kode yang perlu perhatian khusus._