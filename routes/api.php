<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('me', [AuthController::class, 'me'])->name('api.me');

    Route::get('products/lookup', [ProductController::class, 'lookup'])->name('api.products.lookup');
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::apiResource('customers', CustomerController::class)->only(['index', 'show']);
    Route::apiResource('transactions', TransactionController::class)->only(['index', 'show', 'store']);
});
