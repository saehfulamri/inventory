@extends('layouts.app')

@section('title', 'Detail Penerimaan')

@section('content')
    @php
        $statusClass = match ($purchase->status) {
            \App\Enums\PurchaseStatus::Draft => 'badge--warning',
            \App\Enums\PurchaseStatus::Completed => 'badge--success',
            \App\Enums\PurchaseStatus::Cancelled => 'badge--danger',
        };
    @endphp

    <div class="page-header">
        <h1 class="page-title">Penerimaan {{ $purchase->purchase_number }}</h1>
        <a href="{{ route('purchases.index') }}" class="btn btn--ghost">Kembali</a>
    </div>

    <div class="detail-card">
        <dl class="detail-list">
            <dt>Status</dt>
            <dd><span class="badge {{ $statusClass }}">{{ $purchase->status->label() }}</span></dd>
            <dt>Supplier</dt>
            <dd>{{ $purchase->supplier->name }}</dd>
            <dt>Tanggal</dt>
            <dd>{{ $purchase->purchase_date->format('d/m/Y') }}</dd>
            <dt>Dibuat oleh</dt>
            <dd>{{ $purchase->user->name }}</dd>
            <dt>Catatan</dt>
            <dd>{{ $purchase->notes ?: '-' }}</dd>
        </dl>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Beli</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name }} ({{ $item->product->sku }})</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>Rp {{ number_format((float) $purchase->total_amount, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @can('finalize', $purchase)
        @if ($purchase->status === \App\Enums\PurchaseStatus::Draft)
            <div class="form__actions" style="margin-top: 16px;">
                <form method="POST" action="{{ route('purchases.finalize', $purchase) }}"
                    onsubmit="return confirm('Finalisasi penerimaan ini? Stok akan ditambahkan dan data tidak dapat diubah.')">
                    @csrf
                    <button type="submit" class="btn btn--primary">Finalisasi Penerimaan</button>
                </form>
            </div>
        @endif
    @endcan
@endsection