@extends('layouts.app')

@section('title', 'Stok')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Stok</h1>
        <div class="row-actions">
            <a href="{{ route('inventory.movements') }}" class="btn btn--ghost">Riwayat Pergerakan</a>
            @can('create', App\Models\StockAdjustment::class)
                <a href="{{ route('inventory.adjustments.create') }}" class="btn btn--primary">Penyesuaian Stok</a>
            @endcan
        </div>
    </div>

    <form method="GET" action="{{ route('inventory.index') }}" class="filters">
        <div class="form__group">
            <label for="keyword">Cari</label>
            <input type="text" name="keyword" id="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="SKU, barcode, atau nama">
        </div>

        <div class="form__group">
            <label for="category_id">Kategori</label>
            <select name="category_id" id="category_id">
                <option value="">Semua</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <label class="checkbox form__group">
            <input type="checkbox" name="low_stock" value="1" @checked(! empty($filters['low_stock']))>
            Hanya stok menipis
        </label>

        <div class="form__actions">
            <button type="submit" class="btn btn--ghost">Terapkan Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th>Stok</th>
                    <th>Stok Min.</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    @php
                        $isLowStock = (float) $product->stock <= (float) $product->minimum_stock;
                    @endphp
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->unit->name }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->minimum_stock }}</td>
                        <td>
                            @if ($isLowStock)
                                <span class="badge badge--danger">Stok menipis</span>
                            @else
                                <span class="badge badge--success">Aman</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('inventory.movements', ['product_id' => $product->id]) }}" class="btn btn--ghost btn--sm">Riwayat</a>
                                @can('create', App\Models\StockAdjustment::class)
                                    <a href="{{ route('inventory.adjustments.create', ['product_id' => $product->id]) }}" class="btn btn--ghost btn--sm">Sesuaikan</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <p>Belum ada produk.</p>
                                <p class="muted">Tambahkan produk terlebih dahulu untuk melihat stok.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->withQueryString()->links() }}
@endsection
