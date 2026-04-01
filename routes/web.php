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
    Route::get('/discharge', fn() => view('pages.discharge.index'))->name('discharge.index')->middleware('permission:admit patients');
    Route::get('/discharge/{admission}', function (Admission $admission) {
        $admission->load(['patient', 'doctor', 'bed.ward']);
        return view('pages.discharge.summary', compact('admission'));
    })->whereNumber('admission')->name('discharge.summary')->middleware('permission:admit patients');
    Route::get('/discharge/{admission}/print', function (Admission $admission) {
        $admission->load(['patient', 'doctor', 'bed.ward']);
        return view('pages.discharge.summary-print', compact('admission'));
    })->whereNumber('admission')->name('discharge.print')->middleware('permission:admit patients');

    Route::get('/billing', fn() => view('pages.billing.index'))->name('billing.index')->middleware('permission:view billing');
    Route::get('/billing/bills/{bill}/print', function (Bill $bill) {
        $bill->load(['patient', 'items', 'consultation.doctor.department']);
        return view('pages.counter.bill-print', compact('bill'));
    })->whereNumber('bill')->name('billing.bills.print')->middleware('permission:view billing');
});
