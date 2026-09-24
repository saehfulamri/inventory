@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Supplier</h1>
        @can('create', App\Models\Supplier::class)
            <a href="{{ route('suppliers.create') }}" class="btn btn--primary">Tambah Supplier</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('suppliers.index') }}" class="filters">
        <div class="form__group">
            <label for="keyword">Cari</label>
            <input type="text" name="keyword" id="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="Nama atau kode">
        </div>

        <div class="form__group">
            <label for="is_active">Status</label>
            <select name="is_active" id="is_active">
                <option value="">Semua</option>
                <option value="1" @selected(($filters['is_active'] ?? '') == 1)>Aktif</option>
                <option value="0" @selected(array_key_exists('is_active', $filters) && $filters['is_active'] == 0)>Nonaktif</option>
            </select>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--ghost">Terapkan Filter</button>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->code ?? '—' }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>
                            @if ($supplier->phone){{ $supplier->phone }}<br>@endif
                            @if ($supplier->email){{ $supplier->email }}@endif
                        </td>
                        <td>{{ $supplier->address }}</td>
                        <td>
                            @if ($supplier->is_active)
                                <span class="badge badge--success">Aktif</span>
                            @else
                                <span class="badge badge--danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                @can('update', $supplier)
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn--ghost btn--sm">Edit</a>
                                @endcan
                                @can('delete', $supplier)
                                    @if ($supplier->is_active)
                                        <form method="POST" action="{{ route('suppliers.deactivate', $supplier) }}" class="inline" onsubmit="return confirm('Nonaktifkan supplier ini?')">
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
                        <td colspan="6">
                            <div class="empty-state">
                                <p>Belum ada supplier.</p>
                                <p class="muted">Tambahkan supplier pertama untuk mulai mengelola penerimaan barang.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $suppliers->withQueryString()->links() }}
@endsection