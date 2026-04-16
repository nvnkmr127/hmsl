<?php

use App\Http\Controllers\Reports\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'reports', 'as' => 'reports.', 'middleware' => ['permission:view reports']], function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('/visits', [ReportController::class, 'visits'])->name('visits');
        Route::get('/dues', [ReportController::class, 'dues'])->name('dues');
        Route::get('/doctor-consults', [ReportController::class, 'doctorConsults'])->name('doctor-consults');
    });
});
