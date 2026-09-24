<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function findByIdForUpdate(int $id): ?Product;

    public function findActive(): Collection;

    public function searchActive(string $keyword, int $limit = 20): Collection;

    public function findBySku(string $sku): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function countActive(): int;

    public function lowStock(int $limit): Collection;

    public function lowStockCount(): int;

    /**
     * Ringkasan stok terkini (produk aktif): total unit, total nilai (harga beli),
     * jumlah produk aktif, dan jumlah produk yang masuk kategori stok menipis.
     *
     * @return array{total_units: float, total_value: float, active_count: int, low_stock_count: int}
     */
    public function stockSummary(): array;
}
