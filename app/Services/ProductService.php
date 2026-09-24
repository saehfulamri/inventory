<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(protected ProductRepositoryInterface $repository) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Product
    {
        return $this->repository->findById($id);
    }

    public function findActive(): Collection
    {
        return $this->repository->findActive();
    }

    public function searchActive(string $keyword, int $limit = 20): Collection
    {
        return $this->repository->searchActive($keyword, $limit);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->repository->findBySku($sku);
    }

    public function create(array $data): Product
    {
        return $this->repository->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        return $this->repository->update($product, $data);
    }

    public function deactivate(Product $product): Product
    {
        if (! $product->is_active) {
            return $product;
        }

        return $this->repository->update($product, ['is_active' => false]);
    }
}
