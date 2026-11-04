<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
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

    /**
     * Daftar ringkas produk aktif (id + nama) untuk dropdown/laporan.
     */
    public function productOptions(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
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
     */
    public function stockSummary(array $filters = []): array
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

    public function deactivate(Product $product): Product
    {
        if (! $product->is_active) {
            return $product;
        }

        $product->update(['is_active' => false]);

        return $product;
    }
}
