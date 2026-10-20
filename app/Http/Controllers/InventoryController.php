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
use App\Support\RequestFilters;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
        protected StockMovementService $stockMovementService,
        protected StockAdjustmentService $stockAdjustmentService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $filters = $this->stockFilters($request);

        return Inertia::render('Inventory/Index', [
            'products' => $this->productService->paginate($filters),
            'filters' => $filters,
            'categories' => $this->categoryService->findActive(),
        ]);
    }

    public function movements(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $movementTypes = collect(MovementType::cases())
            ->map(fn (MovementType $type) => ['value' => $type->value, 'label' => $type->label()])
            ->values()
            ->all();

        return Inertia::render('Inventory/Movements', [
            'movements' => $this->stockMovementService->paginate($this->movementFilters($request)),
            'filters' => $this->movementFilters($request),
            'products' => $this->productService->findActive(),
            'movementTypes' => $movementTypes,
        ]);
    }

    public function createAdjustment(Request $request): Response
    {
        $this->authorize('create', StockAdjustment::class);

        return Inertia::render('Inventory/Adjustments/Create', [
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
        return RequestFilters::from($request, [
            'keyword' => RequestFilters::CAST_TRIM,
            'category_id' => RequestFilters::CAST_STRING,
            'low_stock' => RequestFilters::CAST_BOOLEAN,
        ]);
    }

    private function movementFilters(Request $request): array
    {
        return RequestFilters::from($request, [
            'product_id' => RequestFilters::CAST_STRING,
            'movement_type' => RequestFilters::CAST_STRING,
            'date_from' => RequestFilters::CAST_STRING,
            'date_to' => RequestFilters::CAST_STRING,
        ]);
    }
}
