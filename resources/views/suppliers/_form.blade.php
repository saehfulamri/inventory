<form method="POST" action="{{ $action }}" class="form">
    @csrf
    @method($method)

    <div class="form__grid">
        <div class="form__group">
            <label for="code">Kode (opsional)</label>
            <input id="code" type="text" name="code" value="{{ old('code', $supplier->code) }}">
            @error('code')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="name">Nama Supplier</label>
            <input id="name" type="text" name="name" value="{{ old('name', $supplier->name) }}" required>
            @error('name')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="phone">Telepon (opsional)</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone', $supplier->phone) }}">
            @error('phone')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="email">Email (opsional)</label>
            <input id="email" type="email" name="email" value="{{ old('email', $supplier->email) }}">
            @error('email')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label for="address">Alamat (opsional)</label>
            <textarea id="address" name="address" rows="3">{{ old('address', $supplier->address) }}</textarea>
            @error('address')
                <span class="form__error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form__group">
            <label class="checkbox">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true))>
                Supplier aktif
            </label>
        </div>
    </div>

    <div class="form__actions">
        <button type="submit" class="btn btn--primary">{{ $submitLabel }}</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn--ghost">Batal</a>
    </div>
</form>