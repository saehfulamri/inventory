@extends('layouts.app')

@section('title', 'Struk '.$sale->sale_number)

@section('content')
    @php
        $saleStatusClass = match ($sale->status) {
            \App\Enums\SaleStatus::Draft => 'badge--warning',
            \App\Enums\SaleStatus::Completed => 'badge--success',
            \App\Enums\SaleStatus::Cancelled => 'badge--danger',
        };
    @endphp

    <div class="page-header print-hidden">
        <h1 class="page-title">Struk Penjualan</h1>
        <div class="row-actions">
            <button type="button" class="btn btn--primary" onclick="window.print()">Cetak Struk</button>
            <a href="{{ route('sales.index') }}" class="btn btn--ghost">Kembali</a>
        </div>
    </div>

    <div class="detail-card receipt">
        <header class="receipt__header">
            <strong class="receipt__brand">{{ config('app.name') }}</strong>
            <span class="receipt__meta">Nomor: {{ $sale->sale_number }}</span>
            <span class="receipt__meta">Tanggal: {{ $sale->sale_date->format('d/m/Y H:i') }}</span>
            <span class="receipt__meta">Kasir: {{ $sale->user->name }}</span>
            <span class="receipt__meta"><span class="badge {{ $saleStatusClass }}">{{ $sale->status->label() }}</span></span>
        </header>

        <div class="table-wrap">
            <table class="table receipt__table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <dl class="receipt__totals">
            <div class="receipt__row">
                <dt>Subtotal</dt>
                <dd>Rp {{ number_format((float) $sale->subtotal, 0, ',', '.') }}</dd>
            </div>
            <div class="receipt__row">
                <dt>Total</dt>
                <dd>Rp {{ number_format((float) $sale->grand_total, 0, ',', '.') }}</dd>
            </div>
            <div class="receipt__row">
                <dt>Metode</dt>
                <dd>{{ $sale->payment_method->label() }}</dd>
            </div>
            <div class="receipt__row">
                <dt>Dibayar</dt>
                <dd>Rp {{ number_format((float) $sale->paid_amount, 0, ',', '.') }}</dd>
            </div>
            <div class="receipt__row">
                <dt>Kembalian</dt>
                <dd>Rp {{ number_format((float) $sale->change_amount, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <p class="receipt__footer muted">Terima kasih atas kunjungan Anda.</p>
    </div>
@endsection