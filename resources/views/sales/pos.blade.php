@extends('layouts.app')

@section('title', 'Penjualan — Transaksi Baru')

@section('content')
    <h1 class="page-title">Penjualan</h1>
    <p class="muted">Cari produk, tambahkan ke keranjang, lalu selesaikan pembayaran.</p>

    <form method="POST" action="{{ route('sales.store') }}" class="form" id="pos-form">
        @csrf
        <input type="hidden" name="sale_date" id="sale_date" value="{{ old('sale_date', now()->toDateString()) }}">

        <div class="pos-layout">
            <section class="pos-pane" aria-label="Pencarian produk">
                <h2 class="section-title">Produk</h2>

                <div class="form__group pos-search">
                    <label for="product-search">Cari produk</label>
                    <div class="pos-search__row">
                        <input type="search" id="product-search" name="q" placeholder="Nama, SKU, atau barcode" autocomplete="off">
                        <button type="button" class="btn btn--ghost" id="search-button">Cari</button>
                    </div>
                </div>

                <ul class="product-list" id="product-results" aria-live="polite">
                    <li class="empty-state">
                        <p>Loading produk…</p>
                    </li>
                </ul>
            </section>

            <section class="pos-pane" aria-label="Keranjang dan pembayaran">
                <h2 class="section-title">Keranjang</h2>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cart-body">
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p>Keranjang masih kosong.</p>
                                        <p class="muted">Tambahkan produk dari daftar di sisi kiri.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="cart-summary">
                    <div class="cart-summary__row">
                        <span>Subtotal</span>
                        <output id="cart-subtotal">Rp 0</output>
                    </div>
                    <div class="cart-summary__row">
                        <span>Total</span>
                        <strong id="cart-grand-total">Rp 0</strong>
                    </div>
                </div>

                <div class="form__grid">
                    <div class="form__group">
                        <label for="payment_method">Metode Pembayaran</label>
                        <select id="payment_method" name="payment_method" required>
                            @foreach ($paymentMethods as $paymentMethod)
                                <option value="{{ $paymentMethod->value }}" @selected(old('payment_method', 'cash') === $paymentMethod->value)>
                                    {{ $paymentMethod->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <span class="form__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form__group" id="paid-group">
                        <label for="paid_amount">Jumlah Bayar</label>
                        <input id="paid_amount" type="number" name="paid_amount" step="0.01" min="0" value="{{ old('paid_amount') }}">
                        @error('paid_amount')
                            <span class="form__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form__group">
                        <label for="change">Kembalian</label>
                        <output id="change" class="stock-figure">—</output>
                    </div>
                </div>

                <div class="form__actions">
                    <button type="submit" class="btn btn--primary" id="pay-button" disabled>Bayar</button>
                    <a href="{{ route('sales.index') }}" class="btn btn--ghost">Batal</a>
                </div>
            </section>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const searchInput = document.getElementById('product-search');
            const searchButton = document.getElementById('search-button');
            const resultsEl = document.getElementById('product-results');
            const cartBody = document.getElementById('cart-body');
            const paymentSelect = document.getElementById('payment_method');
            const paidGroup = document.getElementById('paid-group');
            const paidInput = document.getElementById('paid_amount');
            const changeOutput = document.getElementById('change');
            const subtotalOutput = document.getElementById('cart-subtotal');
            const grandTotalOutput = document.getElementById('cart-grand-total');
            const payButton = document.getElementById('pay-button');
            const form = document.getElementById('pos-form');

            let cart = [];
            let lastProducts = [];

            function formatRupiah(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value));
            }

            function fetchProducts() {
                const q = searchInput.value.trim();
                resultsEl.innerHTML = '<li class="empty-state"><p>Loading produk…</p></li>';

                fetch('{{ route('sales.products') }}?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(function (response) { return response.json(); })
                    .then(function (products) { renderResults(products); })
                    .catch(function () {
                        resultsEl.innerHTML = '<li class="empty-state"><p>Gagal memuat produk.</p></li>';
                    });
            }

            function renderResults(products) {
                lastProducts = products;

                if (products.length === 0) {
                    resultsEl.innerHTML = '<li class="empty-state"><p>Tidak ada produk ditemukan.</p></li>';
                    return;
                }

                resultsEl.innerHTML = '';

                products.forEach(function (product) {
                    const inCart = cart.find(function (item) { return item.id === product.id; });
                    const qty = inCart ? inCart.qty : 0;
                    const soldOut = qty >= product.stock;

                    const li = document.createElement('li');
                    li.className = 'product-item';
                    li.innerHTML =
                        '<div class="product-item__info">' +
                        '<span class="product-item__name"></span>' +
                        '<span class="product-item__meta"></span>' +
                        '</div>' +
                        '<button type="button" class="btn btn--sm product-item__add"></button>';

                    li.querySelector('.product-item__name').textContent = product.name;
                    li.querySelector('.product-item__meta').textContent =
                        product.sku + ' · ' + formatRupiah(product.price) + ' · Stok ' + product.stock;
                    li.querySelector('.product-item__add').textContent = soldOut ? 'Habis' : 'Tambah';
                    li.querySelector('.product-item__add').disabled = soldOut;

                    if (!soldOut) {
                        li.querySelector('.product-item__add').addEventListener('click', function () {
                            const existing = cart.find(function (item) { return item.id === product.id; });

                            if (existing) {
                                if (existing.qty < product.stock) {
                                    existing.qty += 1;
                                }
                            } else {
                                cart.push({ id: product.id, name: product.name, price: product.price, stock: product.stock, qty: 1 });
                            }

                            renderResults(lastProducts);
                            renderCart();
                        });
                    }

                    resultsEl.appendChild(li);
                });
            }

            function renderCart() {
                cartBody.innerHTML = '';

                if (cart.length === 0) {
                    cartBody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><p>Keranjang masih kosong.</p>' +
                        '<p class="muted">Tambahkan produk dari daftar di sisi kiri.</p></div></td></tr>';
                }

                cart.forEach(function (item, index) {
                    const row = document.createElement('tr');

                    row.innerHTML =
                        '<td></td>' +
                        '<td class="cart-price">Rp 0</td>' +
                        '<td><input type="number" class="cart-qty" step="0.001" min="1" max="' + item.stock + '" aria-label="Jumlah ' + item.name + '" value="' + item.qty + '"></td>' +
                        '<td class="cart-line-total">Rp 0</td>' +
                        '<td><button type="button" class="btn btn--danger btn--sm cart-remove" aria-label="Hapus ' + item.name + '">Hapus</button></td>';

                    row.cells[0].textContent = item.name;
                    row.querySelector('.cart-price').textContent = formatRupiah(item.price);
                    row.querySelector('.cart-line-total').textContent = formatRupiah(item.price * item.qty);

                    row.querySelector('.cart-qty').addEventListener('input', function () {
                        let qty = parseFloat(this.value);

                        if (Number.isNaN(qty) || qty < 0.001) {
                            qty = 1;
                        }

                        if (qty > item.stock) {
                            qty = item.stock;
                        }

                        item.qty = qty;
                        this.value = qty;
                        row.querySelector('.cart-line-total').textContent = formatRupiah(item.price * qty);
                        renderResults(lastProducts);
                        recalc();
                    });

                    row.querySelector('.cart-remove').addEventListener('click', function () {
                        cart.splice(index, 1);
                        renderResults(lastProducts);
                        renderCart();
                    });

                    cartBody.appendChild(row);
                });

                recalc();
            }

            function recalc() {
                const subtotal = cart.reduce(function (sum, item) {
                    return sum + (item.price * item.qty);
                }, 0);

                subtotalOutput.textContent = formatRupiah(subtotal);
                grandTotalOutput.textContent = formatRupiah(subtotal);

                payButton.disabled = cart.length === 0;
                updateChange(subtotal);
            }

            function isCash() {
                return paymentSelect.value === 'cash';
            }

            function updateChange(grandTotal) {
                if (!isCash()) {
                    changeOutput.textContent = '—';
                    return;
                }

                const paid = parseFloat(paidInput.value);

                if (Number.isNaN(paid)) {
                    changeOutput.textContent = '—';
                    return;
                }

                changeOutput.textContent = formatRupiah(paid - grandTotal);
            }

            function syncPayment() {
                paidGroup.hidden = !isCash();
                paidInput.required = isCash();

                if (!isCash()) {
                    changeOutput.textContent = '—';
                    return;
                }

                recalc();
            }

            searchButton.addEventListener('click', fetchProducts);
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    fetchProducts();
                }
            });

            paymentSelect.addEventListener('change', syncPayment);
            paidInput.addEventListener('input', recalc);

            form.addEventListener('submit', function () {
                cart.forEach(function (item, index) {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'items[' + index + '][product_id]';
                    idInput.value = item.id;
                    form.appendChild(idInput);

                    const qtyInput = document.createElement('input');
                    qtyInput.type = 'hidden';
                    qtyInput.name = 'items[' + index + '][quantity]';
                    qtyInput.value = item.qty;
                    form.appendChild(qtyInput);
                });
            });

            fetchProducts();
            syncPayment();
        })();
    </script>
@endpush