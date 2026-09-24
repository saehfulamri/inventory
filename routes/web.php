<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('{product}', [ProductController::class, 'update'])->name('update');
        Route::post('{product}/deactivate', [ProductController::class, 'deactivate'])->name('deactivate');
    });

    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::post('{purchase}/finalize', [PurchaseController::class, 'finalize'])->name('finalize');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('movements', [InventoryController::class, 'movements'])->name('movements');
        Route::get('adjustments/create', [InventoryController::class, 'createAdjustment'])->name('adjustments.create');
        Route::post('adjustments', [InventoryController::class, 'storeAdjustment'])->name('adjustments.store');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('movements', [ReportController::class, 'movements'])->name('movements');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('pos', [SaleController::class, 'create'])->name('create');
        Route::get('products', [SaleController::class, 'products'])->name('products');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('{sale}', [SaleController::class, 'show'])->name('show');
    });
});
