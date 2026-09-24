<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'unit'])
            ->when(! empty($filters['keyword']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('name', 'like', "%{$filters['keyword']}%")
                        ->orWhere('sku', 'like', "%{$filters['keyword']}%")
                        ->orWhere('barcode', 'like', "%{$filters['keyword']}%");
                });
            })
            ->when(! empty($filters['category_id']), function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters) {
                $query->where('is_active', $filters['is_active']);
            })
            ->when(! empty($filters['low_stock']), function ($query) {
                $query->whereColumn('stock', '<=', 'minimum_stock');
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::with(['category', 'unit'])->find($id);
    }

    public function findByIdForUpdate(int $id): ?Product
    {
        return Product::whereKey($id)->lockForUpdate()->first();
    }

    public function findActive(): Collection
    {
        return Product::where('is_active', true)->orderBy('name')->get();
    }

    public function searchActive(string $keyword, int $limit = 20): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('sku', 'like', "%{$keyword}%")
                        ->orWhere('barcode', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product;
    }

    public function countActive(): int
    {
        return Product::query()->where('is_active', true)->count();
    }

    public function lowStock(int $limit): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->limit($limit)
            ->get();
    }

    public function lowStockCount(): int
    {
        return Product::query()
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();
    }

    /**
     * Ringkasan stok terkini (produk aktif): total unit, nilai stok (harga beli),
     * jumlah produk aktif, dan jumlah produk stok menipis.
     *
     * DB-agnostic: memakai SUM/COUNT bawaan (tanpa DATE_FORMAT/dialek lain) sehingga
     * aman dijalankan di SQLite (testing) maupun MySQL/PostgreSQL (produksi).
     *
     * @return array{units_total: float, value_total: float, active_count: int, low_stock_count: int}
     */
    public function stockSummary(): array
    {
        $row = Product::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(stock), 0) as units_total')
            ->selectRaw('COALESCE(SUM(stock * purchase_price), 0) as value_total')
            ->first();

        return [
            'units_total' => (float) $row->units_total,
            'value_total' => (float) $row->value_total,
            'active_count' => $this->countActive(),
            'low_stock_count' => $this->lowStockCount(),
        ];
    }
}
