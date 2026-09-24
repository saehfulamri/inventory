<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Generator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ReportService
{
    public function __construct(
        protected SaleRepositoryInterface $sales,
        protected PurchaseRepositoryInterface $purchases,
        protected ProductRepositoryInterface $products,
        protected StockMovementRepositoryInterface $movements,
    ) {}

    /**
     * @return array{total: float, count: int}
     */
    public function salesSummary(array $filters = []): array
    {
        [$from, $to] = $this->period($filters);

        return $this->sales->summaryBetween($from, $to);
    }

    public function paginateSales(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->sales->paginate($this->salesFilters($filters), $perPage);
    }

    /**
     * @return array{total: float, count: int}
     */
    public function purchaseSummary(array $filters = []): array
    {
        [$from, $to] = $this->period($filters);

        return $this->purchases->summaryBetween($from, $to);
    }

    public function paginatePurchases(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->purchases->paginate($this->purchaseFilters($filters), $perPage);
    }

    /**
     * @return array{total_units: float, total_value: float, active_count: int, low_stock_count: int}
     */
    public function stockSummary(array $filters = []): array
    {
        return $this->products->stockSummary();
    }

    public function paginateStock(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->products->paginate($filters, $perPage);
    }

    /**
     * @return array<string, array{quantity: float, count: int}>
     */
    public function movementTotals(array $filters = []): array
    {
        return $this->movements->typeTotals($this->movementFilters($filters));
    }

    public function paginateMovements(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->movements->paginate($filters, $perPage);
    }

    public function productOptions(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Menghasilkan baris CSV dari seluruh penjualan yang cocok dengan filter.
     * Streaming via Generator sehingga tanpa membebani memori, dependency-free.
     *
     * @return Generator<int, string>
     */
    public function exportSales(array $filters = [], int $perPage = 500): Generator
    {
        yield $this->csvLine(['No. Penjualan', 'Tanggal', 'Kasir', 'Metode', 'Total', 'Status']);

        $page = $this->sales->paginate($this->salesFilters($filters), $perPage);

        while ($page->count() > 0) {
            foreach ($page->items() as $sale) {
                yield $this->csvLine([
                    $sale->sale_number,
                    $sale->sale_date->format('Y-m-d'),
                    $sale->user?->name ?? '',
                    $sale->payment_method?->label() ?? '',
                    number_format((float) $sale->grand_total, 2, '.', ''),
                    $sale->status?->label() ?? '',
                ]);
            }

            if (! $page->hasMorePages()) {
                break;
            }

            request()->merge(['page' => $page->currentPage() + 1]);

            $page = $this->sales->paginate($this->salesFilters($filters), $perPage);
        }
    }

    /**
     * @param  array<int, scalar|null>  $row
     */
    private function csvLine(array $row): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $row);
        rewind($handle);

        $line = (string) stream_get_contents($handle);

        fclose($handle);

        return $line;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(array $filters): array
    {
        $now = Carbon::today();

        $from = ! empty($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->startOfDay()
            : $now->copy()->startOfMonth();

        $to = ! empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->endOfDay()
            : $now->copy()->endOfMonth();

        if ($from->gt($to)) {
            throw new InvalidArgumentException('date_from tidak boleh melebihi date_to.');
        }

        return [$from, $to];
    }

    private function salesFilters(array $filters): array
    {
        return array_filter([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ], fn ($value) => $value !== null);
    }

    private function purchaseFilters(array $filters): array
    {
        return array_filter([
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ], fn ($value) => $value !== null);
    }

    private function movementFilters(array $filters): array
    {
        return array_filter([
            'product_id' => $filters['product_id'] ?? null,
            'movement_type' => $filters['movement_type'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
