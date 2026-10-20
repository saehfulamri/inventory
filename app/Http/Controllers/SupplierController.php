<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Support\RequestFilters;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected SupplierService $supplierService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Supplier::class);

        $filters = $this->filters($request);

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $this->supplierService->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Supplier::class);

        return Inertia::render('Suppliers/Create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->supplierService->create($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): Response
    {
        $this->authorize('update', $supplier);

        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->supplierService->update($supplier, $data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function deactivate(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        $this->supplierService->deactivate($supplier);

        return back()->with('success', 'Supplier berhasil dinonaktifkan.');
    }

    private function filters(Request $request): array
    {
        return RequestFilters::from($request, [
            'keyword' => RequestFilters::CAST_TRIM,
            'is_active' => RequestFilters::CAST_INT,
        ]);
    }
}
