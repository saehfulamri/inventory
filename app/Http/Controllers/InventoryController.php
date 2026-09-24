<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\StockAdjustmentService;
use App\Services\StockMovementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
        protected StockMovementService $stockMovementService,
        protected StockAdjustmentService $stockAdjustmentService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        return view('inventory.index', [
            'products' => $this->productService->paginate($this->stockFilters($request)),
            'filters' => $this->stockFilters($request),
            'categories' => $this->categoryService->findActive(),
        ]);
    }

    public function movements(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        return view('inventory.movements', [
            'movements' => $this->stockMovementService->paginate($this->movementFilters($request)),
            'filters' => $this->movementFilters($request),
            'products' => $this->productService->findActive(),
            'movementTypes' => MovementType::cases(),
        ]);
    }

    public function createAdjustment(Request $request): View
    {
        $this->authorize('create', StockAdjustment::class);

        return view('inventory.adjustments.create', [
            'products' => $this->productService->findActive(),
            'selectedProductId' => $request->filled('product_id') ? (int) $request->input('product_id') : null,
        ]);
    }

    public function storeAdjustment(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $this->authorize('create', StockAdjustment::class);

        $data = $request->validated();
        $product = $this->productService->findById((int) $data['product_id']);
        abort_unless($product instanceof Product, 404);

        $this->stockAdjustmentService->adjust($product, (float) $data['new_stock'], $data['reason'], $request->user());

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stok berhasil disesuaikan dan tercatat pada riwayat pergerakan.');
    }

    private function stockFilters(Request $request): array
    {
        return array_filter([
            'keyword' => trim((string) $request->input('keyword')) !== '' ? trim($request->input('keyword')) : null,
            'category_id' => $request->filled('category_id') ? $request->input('category_id') : null,
            'low_stock' => $request->boolean('low_stock') ? true : null,
        ], fn ($value) => $value !== null);
    }

    private function movementFilters(Request $request): array
    {
        return array_filter([
            'product_id' => $request->filled('product_id') ? $request->input('product_id') : null,
            'movement_type' => $request->filled('movement_type') ? $request->input('movement_type') : null,
            'date_from' => $request->filled('date_from') ? $request->input('date_from') : null,
            'date_to' => $request->filled('date_to') ? $request->input('date_to') : null,
        ], fn ($value) => $value !== null);
    }
}
