@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Laporan Stok</h1>
        <a href="{{ route('reports.index') }}" class="btn btn--ghost">← Ringkasan Laporan</a>
    </div>

    <div class="summary-grid" aria-label="Ringkasan stok">
        <div class="summary-card">
            <span class="summary-card__label">Total Unit</span>
            <span class="summary-card__value">{{ number_format((float) ($summary['units'] ?? 0), 2, ',', '.') }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-card__label">Nilai Stok</span>
            <span class="summary-card__value">Rp {{ number_format((float) ($summary['value'] ?? 0), 2, ',', '.') }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-card__label">Produk Aktif</span>
            <span class="summary-card__value">{{ (int) ($summary['active_count'] ?? 0) }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-card__label">Stok Menipis</span>
            <span class="summary-card__value">{{ (int) ($summary['low_stock_count'] ?? 0) }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.stock') }}" class="filters">
        <div class="form__group">
            <label for="keyword">Cari</label>
            <input type="text" name="keyword" id="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="SKU atau nama">
        </div>
        <div class="form__group">
            <label for="low_stock">Status Stok</label>
            <select name="low_stock" id="low_stock">
                <option value="">Semua</option>
                <option value="1" @selected(($filters['low_stock'] ?? '') === '1')>Stok Menipis</option>
            </select>
        </div>
        <div class="form__actions">
            <button type="submit" class="btn btn--ghost">Terapkan Filter</button>
            <a href="{{ route('reports.stock') }}" class="btn btn--ghost">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Stok Min.</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    @php
                        $isLow = (float) $product->stock <= (float) $product->minimum_stock;
                    @endphp
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->minimum_stock }}</td>
                        <td>
                            @if ($isLow)
                                <span class="badge badge--danger">Stok menipis</span>
                            @else
                                <span class="badge badge--success">Aman</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <p>Belum ada produk.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->withQueryString()->links() }}
@endsection
