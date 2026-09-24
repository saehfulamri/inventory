<?php

namespace App\Repositories\Eloquent;

use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentSaleRepository implements SaleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15, ?int $page = null): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['user'])
            ->when(! empty($filters['keyword']), function ($query) use ($filters) {
                $query->where('sale_number', 'like', "%{$filters['keyword']}%");
            })
            ->when(! empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['payment_method']), function ($query) use ($filters) {
                $query->where('payment_method', $filters['payment_method']);
            })
            ->when(! empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('sale_date', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('sale_date', '<=', $filters['date_to']);
            })
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(int $id): ?Sale
    {
        return Sale::with(['user', 'items.product'])->find($id);
    }

    public function nextSaleNumber(DateTimeInterface $date): string
    {
        $prefix = 'SO-'.$date->format('Ymd').'-';

        $latest = Sale::query()
            ->where('sale_number', 'like', $prefix.'%')
            ->orderByDesc('sale_number')
            ->value('sale_number');

        $sequence = $latest === null ? 1 : ((int) substr($latest, strlen($prefix))) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Sale
    {
        return Sale::create($data);
    }

    public function update(Sale $sale, array $data): Sale
    {
        $sale->update($data);

        return $sale;
    }

    public function summaryForDate(string $date): array
    {
        $row = Sale::query()
            ->where('status', SaleStatus::Completed)
            ->whereDate('sale_date', $date)
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total')
            ->selectRaw('COUNT(*) as count')
            ->first();

        return [
            'total' => (float) $row->total,
            'count' => (int) $row->count,
        ];
    }

    public function chartBetween(DateTimeInterface $from, DateTimeInterface $to): array
    {
        $rows = Sale::query()
            ->where('status', SaleStatus::Completed)
            ->whereDate('sale_date', '>=', $from->format('Y-m-d'))
            ->whereDate('sale_date', '<=', $to->format('Y-m-d'))
            ->selectRaw('DATE(sale_date) as day')
            ->selectRaw('SUM(grand_total) as total')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('day')
            ->get();

        $chart = [];

        foreach ($rows as $row) {
            $chart[(string) $row['day']] = [
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ];
        }

        return $chart;
    }

    public function summaryBetween(DateTimeInterface $from, DateTimeInterface $to): array
    {
        $row = Sale::query()
            ->where('status', SaleStatus::Completed)
            ->whereDate('sale_date', '>=', $from->format('Y-m-d'))
            ->whereDate('sale_date', '<=', $to->format('Y-m-d'))
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total')
            ->selectRaw('COUNT(*) as count')
            ->first();

        return [
            'total' => (float) $row->total,
            'count' => (int) $row->count,
        ];
    }
}
