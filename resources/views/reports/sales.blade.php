@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Laporan Penjualan</h1>
        <a href="{{ route('reports.sales.export', request()->query()) }}" class="btn btn--ghost">Unduh CSV</a>
        <a href="{{ route('reports.index') }}" class="btn btn--ghost">← Ringkasan Laporan</a>
    </div>

    <div class="summary-grid" aria-label="Ringkasan penjualan">
        <div class="summary-card">
            <span class="summary-card__label">Total Penjualan</span>
            <span class="summary-card__value">Rp {{ number_format((float) ($summary['total'] ?? 0), 2, ',', '.') }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-card__label">Jumlah Transaksi</span>
            <span class="summary-card__value">{{ (int) ($summary['count'] ?? 0) }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.sales') }}" class="filters">
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
            <a href="{{ route('reports.sales') }}" class="btn btn--ghost">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Penjualan</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Metode</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>{{ $sale->sale_number }}</td>
                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td>{{ $sale->user?->name ?? '—' }}</td>
                        <td>{{ $sale->payment_method?->label() ?? '—' }}</td>
                        <td>Rp {{ number_format((float) $sale->grand_total, 2, ',', '.') }}</td>
                        <td>
                            @php
                                $statusClass = match ($sale->status?->value) {
                                    'completed' => 'badge--success',
                                    'cancelled' => 'badge--danger',
                                    default => 'badge--warning',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $sale->status?->label() ?? '—' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <p>Belum ada penjualan pada periode ini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sales->withQueryString()->links() }}
@endsection
