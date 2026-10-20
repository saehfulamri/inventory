<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use App\Support\RequestFilters;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected CategoryService $categoryService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $filters = $this->filters($request);

        return Inertia::render('Categories/Index', [
            'categories' => $this->categoryService->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Categories/Create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->categoryService->create($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->categoryService->update($category, $data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function deactivate(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->deactivate($category);

        return back()->with('success', 'Kategori berhasil dinonaktifkan.');
    }

    private function filters(Request $request): array
    {
        return RequestFilters::from($request, [
            'keyword' => RequestFilters::CAST_TRIM,
            'is_active' => RequestFilters::CAST_INT,
        ]);
    }
}
