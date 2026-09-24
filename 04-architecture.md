# Architecture — Laravel Service/Repository Pattern

## 1. Architectural Style

Aplikasi menggunakan Laravel monolith dengan:

- MVC sebagai struktur framework.
- Service Layer untuk business logic/use case.
- Repository Layer untuk abstraction akses data.
- Eloquent sebagai ORM.
- Blade sebagai presentation layer.

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

Blade View
    ↑
Controller

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

### Blade

Bertugas:
- presentation,
- rendering data,
- UI interaction ringan.

Jangan menaruh business logic transaksi di Blade.

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
│   ├── layouts/
│   ├── components/
│   ├── dashboard/
│   ├── products/
│   ├── categories/
│   ├── suppliers/
│   ├── purchases/
│   ├── sales/
│   ├── inventory/
│   └── reports/
└── ...

database/
├── migrations/
├── seeders/
└── factories/

tests/
├── Feature/
└── Unit/
```

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

Jangan hanya menyembunyikan tombol di Blade. Backend tetap harus memvalidasi authorization.

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
- membuat query database tersebar di Blade,
- membuat repository baru tanpa kebutuhan,
- menambahkan dependency tanpa alasan,
- mengganti architecture pattern tanpa keputusan eksplisit.

Perubahan arsitektur harus dicatat di changelog.
