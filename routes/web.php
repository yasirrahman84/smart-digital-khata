<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KhataController;
use App\Http\Controllers\LedgerEntryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('customers', CustomerController::class);

    Route::get('customers/{customer}/print', [CustomerController::class, 'print'])->name('customers.print');

    Route::post('customers/{customer}/entries', [LedgerEntryController::class, 'store'])->name('customers.entries.store');
    Route::delete('customers/{customer}/entries/{entry}', [LedgerEntryController::class, 'destroy'])->name('customers.entries.destroy');

    Route::get('khata', [KhataController::class, 'index'])->name('khata.index');
});

require __DIR__.'/auth.php';
