<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:manage_dashboard');

    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    Route::middleware('permission:manage_products')->group(function () {
        Route::get('products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template');
        Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
        Route::post('products/ocr', [ProductController::class, 'ocr'])->name('products.ocr');
        Route::match(['get', 'post'], 'products-bulk/barcode', [ProductController::class, 'bulkBarcode'])->name('products.bulk_barcode');
        Route::get('products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode');
        Route::resource('products', ProductController::class)->except('show');
    });

    Route::middleware('permission:manage_categories')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
    });

    Route::middleware('permission:manage_customers')->group(function () {
        Route::resource('customers', CustomerController::class)->except('show');
    });

    Route::middleware('permission:manage_transactions')->group(function () {
        Route::get('transactions/barcode/lookup', [TransactionController::class, 'lookupByBarcode'])->name('transactions.lookup');
        Route::get('transactions/{transaction}/invoice', [TransactionController::class, 'invoice'])->name('transactions.invoice');
        Route::get('transactions/{transaction}/shipping-label', [TransactionController::class, 'shippingLabel'])->name('transactions.shipping_label');
        Route::resource('transactions', TransactionController::class)->except('destroy');
    });

    Route::middleware('permission:manage_roles')->group(function () {
        Route::resource('roles', RoleController::class)->except('show');
    });

    Route::middleware('permission:manage_users')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });

    Route::middleware('permission:view_reports')->group(function () {
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    });
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');
