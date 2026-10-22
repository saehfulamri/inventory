<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Services\ReportService;
use App\Support\RequestFilters;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReportService $reports,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewReports');

        return Inertia::render('Reports/Index', [
            'sales' => $this->reports->salesSummary(),
            'purchases' => $this->reports->purchaseSummary(),
            'stock' => $this->reports->stockSummary(),
            'movements' => [
                'units' => collect($this->reports->movementTotals())
                    ->sum(fn (array $totals) => abs((float) $totals['quantity'])),
            ],
        ]);
    }

    public function sales(Request $request): Response
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        return Inertia::render('Reports/Sales', [
            'sales' => $this->reports->paginateSales($filters),
            'summary' => $this->reports->salesSummary($filters),
            'filters' => $filters,
        ]);
    }

    public function stock(Request $request): Response
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        return Inertia::render('Reports/Stock', [
            'products' => $this->reports->paginateStock($filters),
            'summary' => $this->reports->stockSummary($filters),
            'filters' => $filters,
        ]);
    }

    public function movements(Request $request): Response
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        $movementTypes = collect(MovementType::cases())
            ->map(fn (MovementType $type) => ['value' => $type->value, 'label' => $type->label()])
            ->values()
            ->all();

        return Inertia::render('Reports/Movements', [
            'movements' => $this->reports->paginateMovements($filters),
            'movementTypes' => $movementTypes,
            'products' => $this->reports->productOptions(),
            'filters' => $filters,
        ]);
    }

    public function purchases(Request $request): Response
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        return Inertia::render('Reports/Purchases', [
            'purchases' => $this->reports->paginatePurchases($filters),
            'summary' => $this->reports->purchaseSummary($filters),
            'filters' => $filters,
        ]);
    }

    public function exportSales(Request $request): StreamedResponse
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);
        $filename = 'laporan-penjualan-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            foreach ($this->reports->exportSales($filters) as $line) {
                echo $line;
            }
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        return RequestFilters::from($request, [
            'keyword' => RequestFilters::CAST_TRIM,
            'product_id' => RequestFilters::CAST_INT,
            'movement_type' => RequestFilters::CAST_STRING,
            'payment_method' => RequestFilters::CAST_STRING,
            'low_stock' => RequestFilters::CAST_STRING,
            'date_from' => RequestFilters::CAST_STRING,
            'date_to' => RequestFilters::CAST_STRING,
        ]);
    }
}
