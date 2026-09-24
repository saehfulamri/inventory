@extends('layouts.app')

@section('title', 'Laporan Pembelian')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Laporan Pembelian</h1>
        <a href="{{ route('reports.index') }}" class="btn btn--ghost">← Ringkasan Laporan</a>
    </div>

    <div class="summary-grid" aria-label="Ringkasan pembelian">
        <div class="summary-card">
            <span class="summary-card__label">Total Pembelian</span>
            <span class="summary-card__value">Rp {{ number_format((float) ($summary['total'] ?? 0), 2, ',', '.') }}</span>
        </div>
        <div class="summary-card">
            <span class="summary-card__label">Jumlah Transaksi</span>
            <span class="summary-card__value">{{ (int) ($summary['count'] ?? 0) }}</span>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.purchases') }}" class="filters">
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
            <a href="{{ route('reports.purchases') }}" class="btn btn--ghost">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Pembelian</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_number }}</td>
                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td>{{ $purchase->supplier?->name ?? '—' }}</td>
                        <td>Rp {{ number_format((float) $purchase->total_amount, 2, ',', '.') }}</td>
                        <td>
                            @php
                                $statusClass = match ($purchase->status?->value) {
                                    'completed' => 'badge--success',
                                    'cancelled' => 'badge--danger',
                                    default => 'badge--warning',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $purchase->status?->label() ?? '—' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <p>Belum ada pembelian pada periode ini.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $purchases->withQueryString()->links() }}
@endsection
