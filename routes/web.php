<?php

use Illuminate\Support\Facades\Route;
use App\Models\Admission;
use App\Models\Bill;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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

Route::middleware(['auth'])->group(function () {
    Route::get('/discharge', [App\Http\Controllers\DischargeController::class, 'index'])->name('discharge.index')->middleware('permission:admit patients');
    Route::get('/discharge/{admission}', [App\Http\Controllers\DischargeController::class, 'show'])->whereNumber('admission')->name('discharge.summary')->middleware('permission:admit patients');
    Route::get('/discharge/{admission}/print', [App\Http\Controllers\DischargeController::class, 'print'])->whereNumber('admission')->name('discharge.print')->middleware('permission:admit patients');

    Route::get('/billing', [App\Http\Controllers\Counter\BillController::class, 'index'])->name('billing.index')->middleware('permission:view billing');
    Route::get('/billing/bills/{bill}/print', [App\Http\Controllers\Counter\BillController::class, 'print'])->whereNumber('bill')->name('billing.bills.print')->middleware('permission:view billing');
});
