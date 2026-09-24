@extends('layouts.app')

@section('title', 'Penerimaan')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Penerimaan</h1>
        @can('create', App\Models\Purchase::class)
            <a href="{{ route('purchases.create') }}" class="btn btn--primary">Tambah Penerimaan</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('purchases.index') }}" class="filters">
        <div class="form__group">
            <label for="keyword">Cari</label>
            <input type="text" name="keyword" id="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="Nomor penerimaan">
        </div>

        <div class="form__group">
            <label for="supplier_id">Supplier</label>
            <select name="supplier_id" id="supplier_id">
                <option value="">Semua</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((string) ($filters['supplier_id'] ?? '') === (string) $supplier->id)>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form__group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="">Semua</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--ghost">Terapkan Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Penerimaan</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_number }}</td>
                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>Rp {{ number_format((float) $purchase->total_amount, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $purchaseStatusClass = match ($purchase->status) {
                                    \App\Enums\PurchaseStatus::Draft => 'badge--warning',
                                    \App\Enums\PurchaseStatus::Completed => 'badge--success',
                                    \App\Enums\PurchaseStatus::Cancelled => 'badge--danger',
                                };
                            @endphp
                            <span class="badge {{ $purchaseStatusClass }}">{{ $purchase->status->label() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn--ghost btn--sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <p>Belum ada penerimaan.</p>
                                <p class="muted">Buat penerimaan pertama untuk mulai mencatat barang masuk.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $purchases->withQueryString()->links() }}
@endsection