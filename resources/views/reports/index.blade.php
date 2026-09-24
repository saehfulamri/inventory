@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
  <div class="page-header">
    <h1 class="page-title">Laporan</h1>
    <p class="muted">Ringkasan penjualan, pembelian, stok, dan pergerakan stok dalam satu tempat.</p>
  </div>

  @can('viewReports')
    <div class="card-grid">
      <a href="{{ route('reports.sales') }}" class="report-card">
        <span class="report-card__title">Penjualan</span>
        <span class="report-card__value">Rp {{ number_format((float) ($sales['total'] ?? 0), 0, ',', '.') }}</span>
        <span class="report-card__meta">{{ (int) ($sales['count'] ?? 0) }} transaksi</span>
      </a>

      <a href="{{ route('reports.purchases') }}" class="report-card">
        <span class="report-card__title">Pembelian</span>
        <span class="report-card__value">Rp {{ number_format((float) ($purchases['total'] ?? 0), 0, ',', '.') }}</span>
        <span class="report-card__meta">{{ (int) ($purchases['count'] ?? 0) }} transaksi</span>
      </a>

      <a href="{{ route('reports.stock') }}" class="report-card">
        <span class="report-card__title">Stok</span>
        <span class="report-card__value">{{ number_format((float) ($stock['units'] ?? 0), 0, ',', '.') }}</span>
        <span class="report-card__meta">unit tercatat</span>
      </a>

      <a href="{{ route('reports.movements') }}" class="report-card">
        <span class="report-card__title">Pergerakan Stok</span>
        <span class="report-card__value">{{ number_format((float) ($movements['units'] ?? 0), 0, ',', '.') }}</span>
        <span class="report-card__meta">unit bergerak</span>
      </a>
    </div>
  @endcan

  @can('viewReports')
    <div class="dashboard-panel">
      <div class="dashboard-panel__header">
        <h2 class="section-title">Ekspor</h2>
      </div>
      <div class="row-actions">
        <a href="{{ route('reports.sales.export') }}" class="btn btn--ghost">Unduh CSV Penjualan</a>
      </div>
    </div>
  @endcan
@endsection
