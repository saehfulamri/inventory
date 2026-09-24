<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseStatus;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Purchase;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\SupplierService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PurchaseService $purchaseService,
        protected SupplierService $supplierService,
        protected ProductService $productService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Purchase::class);

        return view('purchases.index', [
            'purchases' => $this->purchaseService->paginate($this->filters($request)),
            'filters' => $this->filters($request),
            'suppliers' => $this->supplierService->findActive(),
            'statuses' => PurchaseStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Purchase::class);

        return view('purchases.create', [
            'suppliers' => $this->supplierService->findActive(),
            'products' => $this->productService->findActive(),
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $this->authorize('create', Purchase::class);

        $purchase = $this->purchaseService->create($request->validated(), $request->user());

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Penerimaan berhasil dibuat. Finalisasi penerimaan untuk menambah stok.');
    }

    public function show(Purchase $purchase): View
    {
        $this->authorize('view', $purchase);

        return view('purchases.show', [
            'purchase' => $purchase->load(['supplier', 'user', 'items.product']),
        ]);
    }

    public function finalize(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('finalize', $purchase);

        $this->purchaseService->finalize($purchase, $request->user());

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Penerimaan berhasil difinalisasi. Stok telah ditambahkan.');
    }

    private function filters(Request $request): array
    {
        return array_filter([
            'keyword' => trim((string) $request->input('keyword')) !== '' ? trim($request->input('keyword')) : null,
            'supplier_id' => $request->filled('supplier_id') ? $request->input('supplier_id') : null,
            'status' => $request->filled('status') ? $request->input('status') : null,
        ], fn ($value) => $value !== null);
    }
}
