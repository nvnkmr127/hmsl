<?php

use App\Http\Controllers\Master\DepartmentController;
use App\Http\Controllers\Master\DoctorController;
use App\Http\Controllers\Master\ServiceController;
use App\Http\Controllers\Master\MedicineController;
use App\Http\Controllers\Master\LabController;
use App\Http\Controllers\Master\WardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'master', 'as' => 'master.', 'middleware' => ['permission:manage master data']], function () {
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/medicines', [MedicineController::class, 'index'])->name('medicines.index');
        Route::get('/labs', [LabController::class, 'index'])->name('labs.index');
        Route::get('/wards', [WardController::class, 'index'])->name('wards.index');
        Route::get('/beds', [\App\Http\Controllers\Master\BedController::class, 'index'])->name('beds.index');
        Route::get('/inventory-categories', fn() => view('pages.master.inventory-categories'))->name('inventory-categories.index');


        Route::get('/', [DepartmentController::class, 'index'])->name('index');
    });

    Route::group(['prefix' => 'master', 'as' => 'master.'], function () {
        Route::get('/users', [\App\Http\Controllers\Master\UserController::class, 'index'])
            ->name('users.index')
            ->middleware('permission:manage users');
    });
});
