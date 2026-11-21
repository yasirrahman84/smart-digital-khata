<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KhataController;
use App\Http\Controllers\LedgerEntryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('customers', CustomerController::class);

    Route::get('customers/{customer}/print', [CustomerController::class, 'print'])->name('customers.print');

    Route::post('customers/{customer}/entries', [LedgerEntryController::class, 'store'])->name('customers.entries.store');
    Route::delete('customers/{customer}/entries/{entry}', [LedgerEntryController::class, 'destroy'])->name('customers.entries.destroy');

    Route::get('khata', [KhataController::class, 'index'])->name('khata.index');

    Route::resource('products', ProductController::class);
    Route::post('products/{product}/adjust', [ProductController::class, 'adjust'])->name('products.adjust');
    Route::post('products/{product}/activate', [ProductController::class, 'activate'])->name('products.activate');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::post('sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void');

    Route::get('reports/receivables', [ReportController::class, 'receivables'])->name('reports.receivables');
    Route::get('reports/receivables/export', [ReportController::class, 'receivablesCsv'])->name('reports.receivables.csv');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/sales/export', [ReportController::class, 'salesCsv'])->name('reports.sales.csv');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/stock/export', [ReportController::class, 'stockCsv'])->name('reports.stock.csv');
});

require __DIR__.'/auth.php';
