<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected SupplierService $supplierService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        return view('suppliers.index', [
            'suppliers' => $this->supplierService->paginate($this->filters($request)),
            'filters' => $this->filters($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Supplier::class);

        return view('suppliers.create', ['supplier' => new Supplier]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->supplierService->create($data);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);

        return view('suppliers.edit', ['supplier' => $supplier]);
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
        return array_filter([
            'keyword' => trim((string) $request->input('keyword')) !== '' ? trim($request->input('keyword')) : null,
            'is_active' => $request->filled('is_active') ? (int) $request->input('is_active') : null,
        ], fn ($value) => $value !== null);
    }
}
