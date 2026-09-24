@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="muted">Selamat datang, {{ auth()->user()->name }}.</p>
    </div>

    <section class="summary-grid" aria-label="Ringkasan">
        <div class="summary-card">
            <span class="summary-card__label">Penjualan Hari Ini</span>
            <span class="summary-card__value">Rp {{ number_format((int) $summary['today_total'], 0, ',', '.') }}</span>
            <span class="summary-card__meta">{{ $summary['today_count'] }} transaksi</span>
        </div>

        <div class="summary-card">
            <span class="summary-card__label">Transaksi Hari Ini</span>
            <span class="summary-card__value">{{ $summary['today_count'] }}</span>
            <span class="summary-card__meta">Selesai</span>
        </div>

        <div class="summary-card">
            <span class="summary-card__label">Produk Aktif</span>
            <span class="summary-card__value">{{ $summary['active_products'] }}</span>
            <span class="summary-card__meta">Nama produk terdaftar</span>
        </div>

        <div class="summary-card">
            <span class="summary-card__label">Stok Menipis</span>
            <span class="summary-card__value">{{ $summary['low_stock_count'] }}</span>
            <span class="summary-card__meta">Stok ≤ minimum</span>
        </div>
    </section>

    <div class="dashboard-grid">
        <section class="dashboard-panel" aria-labelledby="chart-title">
            <div class="dashboard-panel__header">
                <h2 class="section-title" id="chart-title">Penjualan 7 Hari Terakhir</h2>
            </div>

            <p class="muted chart-summary">Total: Rp {{ number_format((int) collect($summary['chart'])->sum('total'), 0, ',', '.') }}</p>

            @php
                $chartMax = max(1, (int) collect($summary['chart'])->max('total'));
            @endphp
            <ul class="chart" aria-label="Grafik penjualan 7 hari terakhir">
                @foreach ($summary['chart'] as $point)
                    <li class="chart__col">
                        <span class="chart__value">Rp {{ number_format((int) $point['total'], 0, ',', '.') }}</span>
                        <span class="chart__track">
                            <span
                                class="chart__bar"
                                style="{{ $point['total'] > 0 ? 'height: '.max(6, (int) round($point['total'] / $chartMax * 100)).'%' : 'height: 2px' }}"
                            ></span>
                        </span>
                        <span class="chart__label">{{ $point['label'] }}<br>{{ $point['day'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="dashboard-panel" aria-labelledby="low-stock-title">
            <div class="dashboard-panel__header">
                <h2 class="section-title" id="low-stock-title">Produk Stok Menipis</h2>
                @can('viewAny', App\Models\Product::class)
                    <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="text-link">Lihat Semua</a>
                @endcan
            </div>

            @if ($summary['low_stock']->isNotEmpty())
                <ul class="low-stock-list">
                    @foreach ($summary['low_stock'] as $product)
                        <li class="low-stock-item">
                            <span class="low-stock-item__info">
                                <span class="low-stock-item__name">{{ $product->name }}</span>
                                <span class="low-stock-item__meta">{{ $product->sku }}</span>
                            </span>
                            <span class="badge badge--danger">Stok {{ $product->stock }} / min {{ $product->minimum_stock }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="muted">Semua produk berada di atas stok minimum.</p>
            @endif
        </section>
    </div>
@endsection