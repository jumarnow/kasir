<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode');
Route::resource('products', ProductController::class)->except('show');

Route::resource('categories', CategoryController::class)->except('show');
Route::resource('customers', CustomerController::class)->except('show');

Route::get('transactions/barcode/lookup', [TransactionController::class, 'lookupByBarcode'])->name('transactions.lookup');
Route::get('transactions/{transaction}/invoice', [TransactionController::class, 'invoice'])->name('transactions.invoice');
Route::resource('transactions', TransactionController::class)->except('destroy');

Route::resource('roles', RoleController::class)->except('show');
Route::resource('users', UserController::class)->except('show');

Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
