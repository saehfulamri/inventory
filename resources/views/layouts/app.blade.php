<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name'))</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
  <a href="#main" class="skip-link">Lewati ke konten utama</a>
  <header class="topbar">
    <div class="container topbar__inner">
      <a href="{{ route('dashboard') }}" class="brand">{{ config('app.name') }}</a>
      @auth
        <nav class="topnav">
          <div class="topnav__links">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            @can('viewAny', App\Models\Product::class)
              <a href="{{ route('products.index') }}">Produk</a>
            @endcan
            @can('viewAny', App\Models\Supplier::class)
              <a href="{{ route('suppliers.index') }}">Supplier</a>
            @endcan
            @can('viewAny', App\Models\Purchase::class)
              <a href="{{ route('purchases.index') }}">Penerimaan</a>
            @endcan
            @can('viewAny', App\Models\Sale::class)
              <a href="{{ route('sales.index') }}">Penjualan</a>
            @endcan
            @can('viewAny', App\Models\Product::class)
              <a href="{{ route('inventory.index') }}">Stok</a>
            @endcan
            @can('viewReports')
              <a href="{{ route('reports.index') }}">Laporan</a>
            @endcan
          </div>
          <span class="topnav__user">{{ auth()->user()->name }}</span>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn--nav">Keluar</button>
          </form>
        </nav>
      @endauth
    </div>
  </header>

  @if (session('success'))
    <div class="container">
      <div class="flash flash--success" role="status">{{ session('success') }}</div>
    </div>
  @endif

  @if (session('error'))
    <div class="container">
      <div class="flash flash--error" role="alert">{{ session('error') }}</div>
    </div>
  @endif

  <main class="container" id="main" tabindex="-1">
    @yield('content')
  </main>

  @stack('scripts')
</body>

</html>
