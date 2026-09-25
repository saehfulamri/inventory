<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Services\ReportService;
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
        return array_filter([
            'keyword' => trim((string) $request->input('keyword')) !== '' ? trim($request->input('keyword')) : null,
            'product_id' => $request->filled('product_id') ? (int) $request->input('product_id') : null,
            'movement_type' => $request->filled('movement_type') ? $request->input('movement_type') : null,
            'payment_method' => $request->filled('payment_method') ? $request->input('payment_method') : null,
            'low_stock' => $request->filled('low_stock') ? $request->input('low_stock') : null,
            'date_from' => $request->filled('date_from') ? $request->input('date_from') : null,
            'date_to' => $request->filled('date_to') ? $request->input('date_to') : null,
        ], fn ($value) => $value !== null);
    }
}
