<?php

use Illuminate\Support\Facades\Route;
use App\Models\Admission;
use App\Models\Bill;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    if (app()->environment('local')) {
        return redirect()->route('login');
    }

    return view('welcome');
});

// Public Signed Downloads (for webhooks)
Route::get('/public/opd-slip/{id}', [App\Http\Controllers\Counter\OpdController::class, 'download'])
    ->name('public.opd.download')
    ->middleware('signed');

Route::get('/run-seeder', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'WardsAndBedsSeeder']);
    return 'Seeder run successfully!';
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Development Auto-Login
if (app()->environment('local')) {
    Route::get('/autologin', [App\Http\Controllers\Auth\AutoLoginController::class, 'login'])->name('autologin');
}

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
    Route::get('/discharge', [App\Http\Controllers\DischargeController::class, 'index'])->name('discharge.index')->middleware('permission:view ipd');
    Route::get('/discharge/export', [App\Http\Controllers\DischargeController::class, 'export'])->name('discharge.export')->middleware('permission:view ipd');
    Route::get('/discharge/{admission}', [App\Http\Controllers\DischargeController::class, 'show'])->whereNumber('admission')->name('discharge.summary')->middleware('permission:view ipd');
    Route::get('/discharge/{admission}/print', [App\Http\Controllers\DischargeController::class, 'print'])->whereNumber('admission')->name('discharge.print')->middleware('permission:view ipd');
    Route::post('/discharge/{admission}/final-bill', [App\Http\Controllers\DischargeController::class, 'generateFinalBill'])->whereNumber('admission')->name('discharge.final-bill')->middleware('permission:view ipd');

    Route::get('/billing', [App\Http\Controllers\Counter\BillController::class, 'index'])->name('billing.index')->middleware('permission:view billing');
    Route::get('/billing/bills/{bill}/print', [App\Http\Controllers\Counter\BillController::class, 'print'])->whereNumber('bill')->name('billing.bills.print')->middleware('permission:view billing');
    Route::get('/billing/bills/{bill}/pdf', [App\Http\Controllers\Counter\BillController::class, 'pdf'])->whereNumber('bill')->name('billing.bills.pdf');
});
