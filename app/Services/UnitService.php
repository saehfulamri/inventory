<?php

namespace App\Services;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UnitService
{
    public function __construct(protected UnitRepositoryInterface $repository) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Unit
    {
        return $this->repository->findById($id);
    }

    public function findActive(): Collection
    {
        return $this->repository->findActive();
    }

    public function create(array $data): Unit
    {
        return $this->repository->create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        return $this->repository->update($unit, $data);
    }

    public function deactivate(Unit $unit): Unit
    {
        if (! $unit->is_active) {
            return $unit;
        }

        return $this->repository->update($unit, ['is_active' => false]);
    }
}
