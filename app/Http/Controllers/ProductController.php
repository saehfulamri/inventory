<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\UnitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
        protected UnitService $unitService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        return view('products.index', [
            'products' => $this->productService->paginate($this->filters($request)),
            'filters' => $this->filters($request),
            'categories' => $this->categoryService->findActive(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('products.create', $this->formData(new Product));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->productService->create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', $this->formData($product));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->productService->update($product, $data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function deactivate(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deactivate($product);

        return back()->with('success', 'Produk berhasil dinonaktifkan.');
    }

    private function filters(Request $request): array
    {
        return array_filter([
            'keyword' => trim((string) $request->input('keyword')) !== '' ? trim($request->input('keyword')) : null,
            'category_id' => $request->filled('category_id') ? $request->input('category_id') : null,
            'is_active' => $request->filled('is_active') ? (int) $request->input('is_active') : null,
            'low_stock' => $request->boolean('low_stock') ? true : null,
        ], fn ($value) => $value !== null);
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => $this->categoryService->findActive(),
            'units' => $this->unitService->findActive(),
        ];
    }
}
