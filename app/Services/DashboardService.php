<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function __construct(
        protected SaleRepositoryInterface $sales,
        protected ProductRepositoryInterface $products,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $today = $this->sales->summaryForDate(now()->toDateString());

        return [
            'today_total' => $today['total'],
            'today_count' => $today['count'],
            'active_products' => $this->products->countActive(),
            'low_stock_count' => $this->products->lowStockCount(),
            'low_stock' => $this->products->lowStock(5),
            'chart' => $this->salesChart(7),
        ];
    }

    /**
     * @return array<int, array{date: string, label: string, day: string, total: float, count: int}>
     */
    public function salesChart(int $days = 7): array
    {
        $today = Carbon::today();
        $data = $this->sales->chartBetween($today->copy()->subDays($days - 1), $today);

        $points = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $key = $date->toDateString();

            $points[] = [
                'date' => $key,
                'label' => $date->isoFormat('ddd'),
                'day' => $date->format('d/m'),
                'total' => $data[$key]['total'] ?? 0.0,
                'count' => $data[$key]['count'] ?? 0,
            ];
        }

        return $points;
    }
}
