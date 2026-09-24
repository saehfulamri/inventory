@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Penjualan</h1>
        @can('create', App\Models\Sale::class)
            <a href="{{ route('sales.create') }}" class="btn btn--primary">Transaksi Baru</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('sales.index') }}" class="filters">
        <div class="form__group">
            <label for="keyword">Cari</label>
            <input type="text" name="keyword" id="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="Nomor penjualan">
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

        <div class="form__group">
            <label for="payment_method">Metode</label>
            <select name="payment_method" id="payment_method">
                <option value="">Semua</option>
                @foreach ($paymentMethods as $paymentMethod)
                    <option value="{{ $paymentMethod->value }}" @selected(($filters['payment_method'] ?? '') === $paymentMethod->value)>
                        {{ $paymentMethod->label() }}
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
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Penjualan</th>
                    <th>Tanggal</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td>{{ $sale->sale_number }}</td>
                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>Rp {{ number_format((float) $sale->grand_total, 0, ',', '.') }}</td>
                        <td>{{ $sale->payment_method->label() }}</td>
                        <td>
                            @php
                                $saleStatusClass = match ($sale->status) {
                                    \App\Enums\SaleStatus::Draft => 'badge--warning',
                                    \App\Enums\SaleStatus::Completed => 'badge--success',
                                    \App\Enums\SaleStatus::Cancelled => 'badge--danger',
                                };
                            @endphp
                            <span class="badge {{ $saleStatusClass }}">{{ $sale->status->label() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn--ghost btn--sm">Struk</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <p>Belum ada penjualan.</p>
                                <p class="muted">Mulai transaksi pertama dari halaman penjualan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $sales->withQueryString()->links() }}
@endsection