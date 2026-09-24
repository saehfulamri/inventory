@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
    <h1 class="page-title">Edit Supplier</h1>

    <p class="muted">{{ $supplier->code ? $supplier->code.' — ' : '' }}{{ $supplier->name }}</p>

    @include('suppliers._form', [
        'supplier' => $supplier,
        'action' => route('suppliers.update', $supplier),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
@endsection