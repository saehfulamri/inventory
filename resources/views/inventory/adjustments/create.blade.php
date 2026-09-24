@extends('layouts.app')

@section('title', 'Penyesuaian Stok')

@section('content')
    <h1 class="page-title">Penyesuaian Stok</h1>
    <p class="muted">Gunakan penyesuaian untuk mencocokkan stok sistem dengan hasil perhitungan fisik (stok opname).</p>

    <form method="POST" action="{{ route('inventory.adjustments.store') }}" class="form" id="adjustment-form" onsubmit="return confirm('Simpan penyesuaian stok ini? Stok akan diperbarui dan tercatat pada riwayat pergerakan.')">
        @csrf

        <div class="form__group">
            <label for="product_id">Produk</label>
            <select id="product_id" name="product_id" required>
                <option value="">Pilih produk</option>
                @foreach ($products as $product)
                    <option
                        value="{{ $product->id }}"
                        data-stock="{{ $product->stock }}"
                        @selected((string) old('product_id', $selectedProductId) === (string) $product->id)
                    >
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                @endforeach
            </select>
            @error('product_id')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__grid">
            <div class="form__group">
                <label for="current_stock">Stok Saat Ini</label>
                <output id="current_stock" class="stock-figure" aria-live="polite">—</output>
            </div>

            <div class="form__group">
                <label for="new_stock">Stok Baru</label>
                <input id="new_stock" type="number" name="new_stock" step="0.001" min="0" value="{{ old('new_stock') }}" required>
                @error('new_stock')
                    <span class="form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form__group">
                <label for="difference">Selisih</label>
                <output id="difference" class="stock-figure" aria-live="polite">—</output>
            </div>
        </div>

        <div class="form__group">
            <label for="reason">Alasan Penyesuaian</label>
            <textarea id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
            @error('reason')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Simpan Penyesuaian</button>
            <a href="{{ route('inventory.index') }}" class="btn btn--ghost">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const select = document.getElementById('product_id');
            const currentOutput = document.getElementById('current_stock');
            const newStockInput = document.getElementById('new_stock');
            const differenceOutput = document.getElementById('difference');

            function currentStock() {
                const option = select.selectedOptions[0];
                const value = option && option.dataset.stock ? parseFloat(option.dataset.stock) : null;

                return Number.isNaN(value) ? null : value;
            }

            function format(value) {
                return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 }).format(value);
            }

            function update() {
                const stock = currentStock();
                currentOutput.textContent = stock === null ? '—' : format(stock);

                const newStock = parseFloat(newStockInput.value);

                if (stock === null || Number.isNaN(newStock)) {
                    differenceOutput.textContent = '—';
                    return;
                }

                const difference = Math.round((newStock - stock) * 1000) / 1000;
                differenceOutput.textContent = (difference > 0 ? '+' : '') + format(difference);
            }

            select.addEventListener('change', update);
            newStockInput.addEventListener('input', update);
            update();
        })();
    </script>
@endpush
