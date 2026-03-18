<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');
});

// Development Auto-Login
Route::get('/autologin', [App\Http\Controllers\Auth\AutoLoginController::class, 'login'])->name('autologin');

// Settings Module
require __DIR__.'/modules/settings.php';

// Master Data Module
require __DIR__.'/modules/master.php';

// Counter Module
require __DIR__.'/modules/counter.php';

// Doctor Module
require __DIR__.'/modules/doctor.php';

// Reports Module
require __DIR__.'/modules/reports.php';

// Pharmacy Module
require __DIR__.'/modules/pharmacy.php';

// Laboratory Module
require __DIR__.'/modules/laboratory.php';

// Inventory Module
require __DIR__.'/modules/inventory.php';

// Temporary placeholders for missing modules
Route::get('/discharge', fn() => view('pages.discharge.index'))->name('discharge.index');
Route::get('/billing', fn() => view('pages.billing.index'))->name('billing.index');
