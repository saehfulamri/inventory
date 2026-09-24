<?php

namespace App\Repositories\Contracts;

use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UnitRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Unit;

    public function findActive(): Collection;

    public function create(array $data): Unit;

    public function update(Unit $unit, array $data): Unit;
}
