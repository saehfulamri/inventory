@extends('layouts.app')

@section('title', 'Laporan Pergerakan Stok')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Laporan Pergerakan Stok</h1>
        <a href="{{ route('reports.index') }}" class="btn btn--ghost">← Ringkasan Laporan</a>
    </div>

    <form method="GET" action="{{ route('reports.movements') }}" class="filters">
        <div class="form__group">
            <label for="product_id">Produk</label>
            <select name="product_id" id="product_id">
                <option value="">Semua</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected((string) ($filters['product_id'] ?? '') === (string) $product->id)>
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form__group">
            <label for="movement_type">Tipe</label>
            <select name="movement_type" id="movement_type">
                <option value="">Semua</option>
                @foreach ($movementTypes as $movementType)
                    <option value="{{ $movementType->value }}" @selected(($filters['movement_type'] ?? '') === $movementType->value)>
                        {{ $movementType->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form__group">
            <label for="date_from">Dari Tanggal</label>
            <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="form__group">
            <label for="date_to">Sampai Tanggal</label>
            <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <div class="form__actions">
            <button type="submit" class="btn btn--ghost">Terapkan Filter</button>
            <a href="{{ route('reports.movements') }}" class="btn btn--ghost">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Produk</th>
                    <th>Tipe</th>
                    <th>Perubahan</th>
                    <th>Stok</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    @php
                        $delta = (float) $movement->quantity;
                        $deltaClass = $delta >= 0 ? 'badge--success' : 'badge--danger';
                    @endphp
                    <tr>
                        <td>{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->product?->name ?? '—' }}</td>
                        <td><span class="badge badge--ghost">{{ $movement->movement_type?->label() ?? '—' }}</span></td>
                        <td><span class="badge {{ $deltaClass }}">{{ $delta >= 0 ? '+' : '−' }}{{ number_format(abs($delta), 2, ',', '.') }}</span></td>
                        <td>{{ $movement->stock_before }} &rarr; {{ $movement->stock_after }}</td>
                        <td>{{ $movement->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <p>Belum ada pergerakan stok pada filter ini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $movements->withQueryString()->links() }}
@endsection
