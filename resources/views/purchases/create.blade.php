@extends('layouts.app')

@section('title', 'Tambah Penerimaan')

@section('content')
    @php
        $rows = old('items', [['product_id' => '', 'quantity' => '', 'unit_price' => '']]);
    @endphp

    <h1 class="page-title">Tambah Penerimaan</h1>
    <p class="muted">Penerimaan disimpan sebagai draft. Finalisasi setelah form disimpan untuk menambah stok.</p>

    <form method="POST" action="{{ route('purchases.store') }}" class="form" id="purchase-form">
        @csrf

        <div class="form__grid">
            <div class="form__group">
                <label for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id" required>
                    <option value="">Pilih supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <span class="form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form__group">
                <label for="purchase_date">Tanggal</label>
                <input id="purchase_date" type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" required>
                @error('purchase_date')
                    <span class="form__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form__group">
                <label for="notes">Catatan (opsional)</label>
                <input id="notes" type="text" name="notes" value="{{ old('notes') }}">
                @error('notes')
                    <span class="form__error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <h2 class="section-title">Detail Produk</h2>
        @error('items')
            <div class="flash flash--error" role="alert">{{ $message }}</div>
        @enderror

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    @foreach ($rows as $index => $row)
                        <tr class="item-row">
                            <td>
                                <select name="items[{{ $index }}][product_id]" aria-label="Pilih produk">
                                    <option value="">Pilih produk</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-purchase-price="{{ $product->purchase_price }}" @selected((string) ($row['product_id'] ?? '') === (string) $product->id)>
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" class="item-quantity" step="0.001" min="0.001" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? '' }}" aria-label="Jumlah">
                            </td>
                            <td>
                                <input type="number" class="item-price" step="0.01" min="0" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}" aria-label="Harga beli">
                            </td>
                            <td class="item-subtotal">Rp 0</td>
                            <td>
                                <button type="button" class="btn btn--ghost js-remove-row">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="form__actions">
            <button type="button" class="btn btn--ghost" id="add-row">Tambah Baris</button>
            <strong class="grand-total">Total: <span id="grand-total">Rp 0</span></strong>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Simpan Penerimaan</button>
            <a href="{{ route('purchases.index') }}" class="btn btn--ghost">Batal</a>
        </div>
    </form>

    <template id="item-row-template">
        <tr class="item-row">
            <td>
                <select name="items[__INDEX__][product_id]" aria-label="Pilih produk">
                    <option value="">Pilih produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" data-purchase-price="{{ $product->purchase_price }}">
                            {{ $product->name }} ({{ $product->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" class="item-quantity" step="0.001" min="0.001" name="items[__INDEX__][quantity]" aria-label="Jumlah">
            </td>
            <td>
                <input type="number" class="item-price" step="0.01" min="0" name="items[__INDEX__][unit_price]" aria-label="Harga beli">
            </td>
            <td class="item-subtotal">Rp 0</td>
            <td>
                <button type="button" class="btn btn--ghost js-remove-row">Hapus</button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        (function () {
            const body = document.getElementById('items-body');
            const template = document.getElementById('item-row-template');
            const addButton = document.getElementById('add-row');
            let index = {{ count($rows) }};

            function formatRupiah(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
            }

            function recalc() {
                let total = 0;

                body.querySelectorAll('.item-row').forEach(function (row) {
                    const qty = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const subtotal = Math.round(qty * price * 100) / 100;

                    row.querySelector('.item-subtotal').textContent = formatRupiah(subtotal);
                    total += subtotal;
                });

                document.getElementById('grand-total').textContent = formatRupiah(total);
            }

            function bindRow(row) {
                row.querySelectorAll('.item-quantity, .item-price').forEach(function (input) {
                    input.addEventListener('input', recalc);
                });

                row.querySelector('select').addEventListener('change', function () {
                    const price = this.selectedOptions[0] ? this.selectedOptions[0].dataset.purchasePrice : null;
                    const priceInput = row.querySelector('.item-price');

                    if (price && priceInput.value === '') {
                        priceInput.value = price;
                    }

                    recalc();
                });

                row.querySelector('.js-remove-row').addEventListener('click', function () {
                    if (body.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                        recalc();
                    }
                });

                recalc();
            }

            addButton.addEventListener('click', function () {
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.item-row');

                row.querySelectorAll('[name]').forEach(function (el) {
                    el.name = el.name.replace('__INDEX__', index);
                });

                index++;
                body.appendChild(row);
                bindRow(row);
            });

            body.querySelectorAll('.item-row').forEach(bindRow);
        })();
    </script>
@endpush