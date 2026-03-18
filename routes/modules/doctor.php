<?php

use App\Http\Controllers\Doctor\DoctorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'doctor', 'as' => 'doctor.'], function () {
        Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard')->middleware('permission:edit case sheets');
        Route::get('/appointments', [\App\Http\Controllers\Doctor\AppointmentController::class, 'index'])->name('appointments.index')->middleware('permission:edit case sheets');
        Route::get('/patients', [\App\Http\Controllers\Doctor\PatientController::class, 'index'])->name('patients.index')->middleware('permission:view patients');
    });
});
