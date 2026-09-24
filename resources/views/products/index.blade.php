@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Produk</h1>
        @can('create', App\Models\Product::class)
            <a href="{{ route('products.create') }}" class="btn btn--primary">Tambah Produk</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="filters">
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

        <div class="form__group">
            <label for="is_active">Status</label>
            <select name="is_active" id="is_active">
                <option value="">Semua</option>
                <option value="1" @selected(($filters['is_active'] ?? '') == 1)>Aktif</option>
                <option value="0" @selected(array_key_exists('is_active', $filters) && $filters['is_active'] == 0)>Nonaktif</option>
            </select>
        </div>

        <label class="checkbox form__group">
            <input type="checkbox" name="low_stock" value="1" @checked(! empty($filters['low_stock']))>
            Stok minimum
        </label>

        <div class="form__actions">
            <button type="submit" class="btn btn--ghost">Terapkan Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>SKU</th>
                    <th>Barcode</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Stok Min.</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            @if ($product->image_path)
                                <img src="{{ $product->image_url }}" alt="Foto {{ $product->name }}" class="thumb" loading="lazy">
                            @else
                                <span class="thumb thumb--empty" aria-hidden="true">—</span>
                            @endif
                        </td>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->barcode }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>Rp {{ number_format((float) $product->purchase_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>{{ $product->minimum_stock }}</td>
                        <td>
                            @if ($product->is_active)
                                <span class="badge badge--success">Aktif</span>
                            @else
                                <span class="badge badge--danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                @can('update', $product)
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn--ghost btn--sm">Edit</a>
                                @endcan
                                @can('delete', $product)
                                    @if ($product->is_active)
                                        <form method="POST" action="{{ route('products.deactivate', $product) }}" class="inline" onsubmit="return confirm('Nonaktifkan produk ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn--danger btn--sm">Nonaktifkan</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <p>Belum ada produk.</p>
                                <p class="muted">Tambahkan produk pertama untuk mulai mengelola inventori.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->withQueryString()->links() }}
@endsection