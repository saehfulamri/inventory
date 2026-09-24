<form method="POST" action="{{ $action }}" class="form">
    @csrf
    @method($method)

    <div class="form__grid">
        <div class="form__group">
            <label for="name">Nama Produk</label>
            <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required>
            @error('name')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="sku">SKU / Kode</label>
            <input id="sku" type="text" name="sku" value="{{ old('sku', $product->sku) }}" required>
            @error('sku')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="barcode">Barcode (opsional)</label>
            <input id="barcode" type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}">
            @error('barcode')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="unit_id">Satuan</label>
            <select id="unit_id" name="unit_id" required>
                <option value="">Pilih satuan</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) old('unit_id', $product->unit_id) === (string) $unit->id)>
                        {{ $unit->name }} ({{ $unit->symbol }})
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="purchase_price">Harga Beli</label>
            <input id="purchase_price" type="number" name="purchase_price" step="0.01" min="0" value="{{ old('purchase_price', $product->purchase_price) }}" required>
            @error('purchase_price')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="selling_price">Harga Jual</label>
            <input id="selling_price" type="number" name="selling_price" step="0.01" min="0" value="{{ old('selling_price', $product->selling_price) }}" required>
            @error('selling_price')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="minimum_stock">Stok Minimum</label>
            <input id="minimum_stock" type="number" name="minimum_stock" step="0.001" min="0" value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
            @error('minimum_stock')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label class="checkbox">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
                Produk aktif
            </label>
        </div>
    </div>

    <div class="form__actions">
        <button type="submit" class="btn btn--primary">{{ $submitLabel }}</button>
        <a href="{{ route('products.index') }}" class="btn btn--ghost">Batal</a>
    </div>
</form>