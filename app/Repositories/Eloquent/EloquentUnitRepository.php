<?php

namespace App\Repositories\Eloquent;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentUnitRepository implements UnitRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Unit::query()
            ->when(! empty($filters['keyword']), function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['keyword']}%");
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters) {
                $query->where('is_active', $filters['is_active']);
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Unit
    {
        return Unit::find($id);
    }

    public function findActive(): Collection
    {
        return Unit::where('is_active', true)->orderBy('name')->get();
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit;
    }
}
