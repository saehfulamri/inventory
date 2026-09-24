<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Sale;
use App\Services\ProductService;
use App\Services\SaleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected SaleService $saleService,
        protected ProductService $productService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sale::class);

        return view('sales.index', [
            'sales' => $this->saleService->paginate($this->filters($request)),
            'filters' => $this->filters($request),
            'statuses' => SaleStatus::cases(),
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Sale::class);

        return view('sales.pos', [
            'paymentMethods' => PaymentMethod::cases(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $this->authorize('create', Sale::class);

        $products = $this->productService->searchActive(trim((string) $request->input('q')));

        return response()->json($products->map(fn ($product) => [
            'id' => $product->getKey(),
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => (float) $product->selling_price,
            'stock' => (float) $product->stock,
        ]));
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $this->authorize('create', Sale::class);

        $sale = $this->saleService->complete($request->validated(), $request->user());

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Transaksi berhasil. Stok telah diperbarui.');
    }

    public function show(Sale $sale): View
    {
        $this->authorize('view', $sale);

        return view('sales.show', [
            'sale' => $sale->load(['user', 'items.product']),
        ]);
    }

    private function filters(Request $request): array
    {
        return array_filter([
            'keyword' => trim((string) $request->input('keyword')) !== '' ? trim($request->input('keyword')) : null,
            'status' => $request->filled('status') ? $request->input('status') : null,
            'payment_method' => $request->filled('payment_method') ? $request->input('payment_method') : null,
            'date_from' => $request->filled('date_from') ? $request->input('date_from') : null,
            'date_to' => $request->filled('date_to') ? $request->input('date_to') : null,
        ], fn ($value) => $value !== null);
    }
}
