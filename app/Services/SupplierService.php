<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierService
{
    public function __construct(protected SupplierRepositoryInterface $repository) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Supplier
    {
        return $this->repository->findById($id);
    }

    public function findActive(): Collection
    {
        return $this->repository->findActive();
    }

    public function create(array $data): Supplier
    {
        return $this->repository->create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return $this->repository->update($supplier, $data);
    }

    public function deactivate(Supplier $supplier): Supplier
    {
        if (! $supplier->is_active) {
            return $supplier;
        }

        return $this->repository->update($supplier, ['is_active' => false]);
    }
}
