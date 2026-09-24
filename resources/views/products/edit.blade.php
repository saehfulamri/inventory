@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
    <h1 class="page-title">Edit Produk</h1>

    <p class="muted">{{ $product->sku }} — {{ $product->name }}</p>

    @include('products._form', [
        'product' => $product,
        'categories' => $categories,
        'units' => $units,
        'action' => route('products.update', $product),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
@endsection