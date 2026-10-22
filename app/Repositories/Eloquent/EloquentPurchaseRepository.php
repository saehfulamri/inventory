<?php

namespace App\Repositories\Eloquent;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPurchaseRepository implements PurchaseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Purchase::query()
            ->with(['supplier', 'user'])
            ->when(! empty($filters['keyword']), function ($query) use ($filters) {
                $query->where('purchase_number', 'like', "%{$filters['keyword']}%");
            })
            ->when(! empty($filters['supplier_id']), function ($query) use ($filters) {
                $query->where('supplier_id', $filters['supplier_id']);
            })
            ->when(! empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('purchase_date', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('purchase_date', '<=', $filters['date_to']);
            })
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Purchase
    {
        return Purchase::with(['supplier', 'user', 'items.product'])->find($id);
    }

    public function findByIdForUpdate(int $id): ?Purchase
    {
        return Purchase::query()->whereKey($id)->lockForUpdate()->first();
    }

    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        $purchase->update($data);

        return $purchase;
    }

    public function summaryBetween(DateTimeInterface $from, DateTimeInterface $to): array
    {
        $row = Purchase::query()
            ->where('status', PurchaseStatus::Completed)
            ->whereDate('purchase_date', '>=', $from->format('Y-m-d'))
            ->whereDate('purchase_date', '<=', $to->format('Y-m-d'))
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total')
            ->selectRaw('COUNT(*) as count')
            ->first();

        return [
            'total' => (float) $row->total,
            'count' => (int) $row->count,
        ];
    }
}
