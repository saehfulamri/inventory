<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\UnitService;
use App\Support\RequestFilters;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected UnitService $unitService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Unit::class);

        $filters = $this->filters($request);

        return Inertia::render('Units/Index', [
            'units' => $this->unitService->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Unit::class);

        return Inertia::render('Units/Create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $this->authorize('create', Unit::class);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->unitService->create($data);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit): Response
    {
        $this->authorize('update', $unit);

        return Inertia::render('Units/Edit', [
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $unit);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $this->unitService->update($unit, $data);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function deactivate(Unit $unit): RedirectResponse
    {
        $this->authorize('delete', $unit);

        $this->unitService->deactivate($unit);

        return back()->with('success', 'Satuan berhasil dinonaktifkan.');
    }

    private function filters(Request $request): array
    {
        return RequestFilters::from($request, [
            'keyword' => RequestFilters::CAST_TRIM,
            'is_active' => RequestFilters::CAST_INT,
        ]);
    }
}
