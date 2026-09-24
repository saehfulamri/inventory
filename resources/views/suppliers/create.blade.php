@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
    <h1 class="page-title">Tambah Supplier</h1>

    @include('suppliers._form', [
        'supplier' => $supplier,
        'action' => route('suppliers.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Supplier',
    ])
@endsection