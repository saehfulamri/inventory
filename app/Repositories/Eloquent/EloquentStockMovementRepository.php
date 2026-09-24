<?php

namespace App\Repositories\Eloquent;

use App\Models\StockMovement;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['product', 'user'])
            ->when(! empty($filters['product_id']), function ($query) use ($filters) {
                $query->where('product_id', $filters['product_id']);
            })
            ->when(! empty($filters['movement_type']), function ($query) use ($filters) {
                $query->where('movement_type', $filters['movement_type']);
            })
            ->when(! empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Total kuantitas & jumlah catatan per tipe pergerakan sesuai filter.
     *
     * DB-agnostic: SUM/COUNT + group by movement_type (bukan DATE_FORMAT/dialek
     * spesifik DB), jadi aman di SQLite (testing) dan MySQL/PostgreSQL.
     *
     * @return array<string, array{quantity: float, count: int}>
     */
    public function typeTotals(array $filters = []): array
    {
        return StockMovement::query()
            ->when(! empty($filters['product_id']), function ($query) use ($filters) {
                $query->where('product_id', $filters['product_id']);
            })
            ->when(! empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            })
            ->selectRaw('movement_type')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('movement_type')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->movement_type->value => [
                    'quantity' => (float) $row->quantity,
                    'count' => (int) $row->count,
                ]];
            })
            ->all();
    }
}
