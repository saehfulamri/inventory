<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Services\ReportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ReportService $reports,
    ) {}

    public function index(): View
    {
        $this->authorize('viewReports');

        return view('reports.index', [
            'salesSummary' => $this->reports->salesSummary(),
            'purchaseSummary' => $this->reports->purchaseSummary(),
            'stockSummary' => $this->reports->stockSummary(),
        ]);
    }

    public function sales(Request $request): View
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        return view('reports.sales', [
            'sales' => $this->reports->paginateSales($filters),
            'summary' => $this->reports->salesSummary($filters),
            'filters' => $filters,
        ]);
    }

    public function stock(Request $request): View
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        return view('reports.stock', [
            'products' => $this->reports->paginateStock($filters),
            'summary' => $this->reports->stockSummary($filters),
            'filters' => $filters,
        ]);
    }

    public function movements(Request $request): View
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        return view('reports.movements', [
            'movements' => $this->reports->paginateMovements($filters),
            'totals' => $this->reports->movementTotals($filters),
            'movementTypes' => MovementType::cases(),
            'products' => $this->reports->productOptions(),
            'filters' => $filters,
        ]);
    }

    public function purchases(Request $request): View
    {
        $this->authorize('viewReports');

        $filters = $this->filters($request);

        return view('reports.purchases', [
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
            'product_id' => $request->filled('product_id') ? (int) $request->input('product_id') : null,
            'movement_type' => $request->filled('movement_type') ? $request->input('movement_type') : null,
            'payment_method' => $request->filled('payment_method') ? $request->input('payment_method') : null,
            'date_from' => $request->filled('date_from') ? $request->input('date_from') : null,
            'date_to' => $request->filled('date_to') ? $request->input('date_to') : null,
        ], fn ($value) => $value !== null);
    }
}
