# Architecture — Laravel Service/Repository Pattern

## 1. Architectural Style

Aplikasi menggunakan Laravel monolith dengan:

- MVC sebagai struktur framework.
- Service Layer untuk business logic/use case.
- Repository Layer untuk abstraction akses data.
- Eloquent sebagai ORM.
- Vue.js 3 + Inertia.js sebagai presentation layer (SPA; Blade hanya untuk root template).

## 2. Prinsip Dependency

Alur utama:

Controller
    ↓
Service
    ↓
Repository
    ↓
Eloquent Model
    ↓
MySQL

Presentation:

Vue Page (Inertia)
    ↑
Controller (Inertia::render)

Request validation:

HTTP Request
    ↓
Form Request
    ↓
Controller
    ↓
Service

## 3. Responsibility

### Controller

Controller bertugas:
- menerima HTTP request,
- menggunakan Form Request,
- memanggil Service,
- menentukan response/redirect/view.

Controller TIDAK boleh menjadi tempat business logic kompleks.

Contoh:

```php
public function store(StoreProductRequest $request)
{
    $this->productService->create($request->validated());

    return redirect()
        ->route('products.index')
        ->with('success', 'Produk berhasil dibuat.');
}
```

### Form Request

Bertugas:
- validation,
- authorization request bila sesuai.

### Service

Bertugas:
- business rules,
- orchestration,
- database transaction,
- koordinasi beberapa repository,
- memastikan invariants bisnis.

Contoh:

```php
DB::transaction(function () use ($data) {
    $sale = $this->saleRepository->create($data);
    // create details
    // update stock
    // create stock movements
});
```

### Repository

Bertugas:
- query/persistensi data,
- encapsulation query yang reusable,
- retrieval/filtering.

Repository tidak seharusnya menentukan keseluruhan business workflow.

### Model

Bertugas:
- representasi entity/database,
- relationship,
- casts,
- model-specific behavior sederhana.

### Vue (Inertia Page)

Bertugas:
- presentation,
- rendering props dari controller,
- interaksi UI ringan (state lokal komponen, form, navigasi via Inertia).

Jangan menaruh business logic transaksi di Vue. Perhitungan bisnis (subtotal, grand total, stok) tetap dilakukan server-side oleh Service; kalkulasi di sisi client hanya untuk preview/UX dan bukan sumber kebenaran.

## 4. Suggested Directory Structure

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
├── Services/
├── Policies/
└── Support/

resources/
├── views/
│   └── app.blade.php              # root template (satu-satunya view Blade)
└── js/
    ├── app.js
    ├── app.css
    ├── Pages/
    │   ├── Auth/
    │   ├── Dashboard/
    │   ├── Products/
    │   ├── Categories/
    │   ├── Suppliers/
    │   ├── Purchases/
    │   ├── Sales/
    │   ├── Inventory/
    │   └── Reports/
    └── Components/
        ├── Layouts/
        └── ui/

database/
├── migrations/
├── seeders/
└── factories/

tests/
├── Feature/
└── Unit/
```

### Inertia/Vue Conventions

- Controller mengembalikan `Inertia::render('PageName', [...props])`, bukan `view()`.
- Data yang dibagi lintas halaman (user login, flash message) dibagikan lewat middleware `HandleInertiaRequests` (shared props), tidak diulang per controller.
- Navigasi internal memakai Inertia (`<Link>` / `router`); route memakai named route + Ziggy.
- Form memakai `useForm` dari `@inertiajs/vue3` — CSRF/XSRF dan error handling otomatis, error validasi server diasosiasikan ke input.
- Pagination: controller mengirim `LengthAwarePaginator`; Inertia mengubahnya menjadi `data/links/meta` untuk komponen frontend.
- Halaman error (403/404/500) dirender lewat root component `Error.vue`.
- `resources/views/app.blade.php` adalah satu-satunya view Blade (root template).

## 5. Repository Contract

Jika repository abstraction digunakan:

```php
interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findBySku(string $sku): ?Product;

    public function paginate(array $filters, int $perPage = 15);

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;
}
```

Implementasi:

```php
class EloquentProductRepository implements ProductRepositoryInterface
{
    // Eloquent queries
}
```

Binding dilakukan melalui Service Provider.

## 6. Service Naming

Gunakan nama berbasis use case, misalnya:

- ProductService
- SaleService
- PurchaseService
- StockService
- ReportService

Jangan membuat satu `GlobalService` yang menangani semua proses.

## 7. Transaction Boundary

Service menjadi tempat utama transaction boundary untuk workflow yang memodifikasi beberapa data.

Contoh penjualan:

```text
SaleService
   │
   ├── create sale
   ├── create sale details
   ├── validate stock
   ├── decrease stock
   └── create stock movements

Semua berada dalam satu DB transaction.
```

Jika satu langkah gagal, perubahan harus rollback.

## 8. Stock Integrity

Stok tidak boleh diubah secara sembarang dari berbagai controller.

Semua perubahan stok harus melalui mekanisme service yang terkontrol.

Contoh sumber movement:
- PURCHASE_IN
- SALE_OUT
- ADJUSTMENT
- RETURN_IN
- VOID_REVERSAL bila diperlukan

## 9. Authorization

Gunakan Laravel authorization mechanism yang sesuai:
- Gates
- Policies
- middleware/role authorization

Jangan hanya menyembunyikan menu/tombol di frontend (Vue). Backend tetap harus memvalidasi authorization, dan data yang tidak diizinkan tidak boleh dikirim sebagai props.

## 10. Database Query Rules

- Gunakan Eloquent/query builder.
- Hindari N+1 query.
- Gunakan eager loading jika diperlukan.
- Gunakan pagination untuk daftar.
- Tambahkan index berdasarkan kebutuhan query nyata.
- Jangan menggunakan `SELECT *` jika query/report memerlukan subset data yang jelas dan besar.

## 11. Error Handling

Expected business errors harus ditangani secara user-friendly.

Unexpected errors:
- dicatat melalui logging,
- tidak menampilkan detail internal kepada user.

## 12. Testing Strategy

### Unit Test
Untuk business logic Service yang dapat diuji secara terisolasi.

### Feature Test
Untuk:
- authentication,
- authorization,
- HTTP flow,
- database behavior,
- transaksi penting.

Prioritas testing:
1. Sale
2. Stock
3. Purchase/Receiving
4. Authorization
5. Product validation

## 13. Architectural Constraints

AI/developer tidak boleh:
- memindahkan business logic ke Controller hanya demi cepat selesai,
- membuat query database tersebar di view/frontend,
- membuat repository baru tanpa kebutuhan,
- menambahkan dependency tanpa alasan,
- mengganti architecture pattern tanpa keputusan eksplisit.

Perubahan arsitektur harus dicatat di changelog.
