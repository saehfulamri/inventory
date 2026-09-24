@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
    <h1 class="page-title">Tambah Produk</h1>

    @include('products._form', [
        'product' => $product,
        'categories' => $categories,
        'units' => $units,
        'action' => route('products.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Produk',
    ])
@endsection