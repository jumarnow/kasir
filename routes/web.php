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

        Route::resource('finishings', App\Http\Controllers\FinishingController::class)->except('show');
        // Route::resource('materials', App\Http\Controllers\MaterialController::class)->except('show');
        // Route::resource('displays', App\Http\Controllers\DisplayController::class)->except('show');
    });

    Route::middleware('permission:manage_categories')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
    });

    Route::middleware('permission:view_customers')->group(function () {
        Route::resource('customers', CustomerController::class);
    });

    Route::middleware('permission:manage_transactions')->group(function () {
        Route::get('transactions/barcode/lookup', [TransactionController::class, 'lookupByBarcode'])->name('transactions.lookup');
        Route::get('transactions/export', [TransactionController::class, 'exportExcel'])->name('transactions.export');
        Route::get('transactions/{transaction}/invoice', [TransactionController::class, 'invoice'])->name('transactions.invoice');
        Route::get('transactions/{transaction}/spk', [TransactionController::class, 'spk'])->name('transactions.spk');
        Route::get('transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
        Route::get('transactions/{transaction}/invoice-a5', [TransactionController::class, 'invoiceA5'])->name('transactions.invoice_a5');
        Route::get('transactions/{transaction}/shipping-label', [TransactionController::class, 'shippingLabel'])->name('transactions.shipping_label');

        // Payments
        Route::post('transactions/{transaction}/pay', [TransactionController::class, 'storePayment'])->name('transactions.payments.store');

        Route::resource('transactions', TransactionController::class);
    });

    Route::middleware('permission:manage_roles')->group(function () {
        Route::resource('roles', RoleController::class)->except('show');
    });

    Route::middleware('permission:manage_users')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });

    Route::middleware('permission:view_reports')->group(function () {
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/sales/export', [ReportController::class, 'exportSalesExcel'])->name('reports.sales.export');
        Route::get('reports/profit', [App\Http\Controllers\ProfitReportController::class, 'index'])->name('reports.profit');
        Route::get('reports/profit/export', [App\Http\Controllers\ProfitReportController::class, 'export'])->name('reports.profit.export');
    });

    // Expense Management
    Route::middleware('permission:manage_transactions')->group(function () {
        Route::resource('expense-categories', App\Http\Controllers\ExpenseCategoryController::class)->except('show');
        Route::resource('expenses', App\Http\Controllers\ExpenseController::class)->except('show');
    });

    Route::prefix('payroll')->group(function () {
        Route::resource('employees', App\Http\Controllers\EmployeeController::class);

        Route::post('payrolls/{payroll}/mark-paid', [App\Http\Controllers\PayrollController::class, 'markPaid'])->name('payrolls.mark-paid');
        Route::get('payrolls/{payroll}/print', [App\Http\Controllers\PayrollController::class, 'print'])->name('payrolls.print');
        Route::resource('payrolls', App\Http\Controllers\PayrollController::class);
    });
});

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware('auth')
    ->name('home');
