<?php

use App\Http\Controllers\Counter\PatientController;
use App\Http\Controllers\Counter\OpdController;
use App\Http\Controllers\Counter\AddressAutocompleteController;
use App\Http\Controllers\Counter\ConsentController;
use App\Http\Controllers\IPD\IpdController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'counter', 'as' => 'counter.'], function () {
        // Patients
        Route::group(['middleware' => ['permission:view patients']], function() {
            Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
            Route::get('/patients/{id}/history', function($id) {
                return view('pages.counter.history', ['id' => $id]);
            })->name('patients.history');
            Route::get('/patients/{id}/consents/high-risk', [ConsentController::class, 'highRisk'])->name('patients.consents.high-risk');
            Route::get('/patients/{id}/consents/high-risk/print', [ConsentController::class, 'highRiskPrint'])->name('patients.consents.high-risk.print');
            Route::get('/patients/{id}/consents/high-risk/en', [ConsentController::class, 'highRiskEnglish'])->name('patients.consents.high-risk.en');
            Route::get('/patients/{id}/consents/high-risk/print/en', [ConsentController::class, 'highRiskPrintEnglish'])->name('patients.consents.high-risk.print.en');
            Route::get('/address-autocomplete', AddressAutocompleteController::class)->name('address.autocomplete')->middleware('throttle:30,1');
        });
        
        // OPD
        Route::get('/opd', [OpdController::class, 'index'])->name('opd.index')->middleware('permission:view opd');
        Route::get('/opd/{id}/print', [OpdController::class, 'print'])->name('opd.print')->middleware('permission:view opd');
        Route::get('/opd/{id}/download', [OpdController::class, 'download'])->name('opd.download')->middleware('permission:view opd');
        
        // IPD Routes
        Route::group(['middleware' => ['permission:view ipd']], function() {
            Route::get('/ipd', fn() => view('pages.ipd.index'))->name('ipd.index');
            Route::get('/ipd/create', fn() => view('pages.ipd.create'))->name('ipd.create');
            Route::get('/ipd/{admission}', [IpdController::class, 'show'])->name('ipd.show');
            Route::get('/ipd/{admission}/discharge', [IpdController::class, 'dischargeSummary'])->name('ipd.discharge');
            Route::get('/ipd/{admission}/discharge/print', [IpdController::class, 'printSummary'])->name('discharge.summary.print');
        });

        // Billing
        Route::group(['middleware' => ['permission:view billing']], function() {
            Route::get('/billing', fn() => redirect()->route('billing.index'))->name('billing.index');
            Route::get('/bills/{id}/print', function($id) {
                $bill = \App\Models\Bill::with(['patient', 'items', 'consultation.doctor.department'])->findOrFail($id);
                return view('pages.counter.bill-print', compact('bill'));
            })->name('bills.print');
        });

        // Prescriptions (Counter can also print them)
        Route::get('/prescriptions/{id}/print', function($id) {
            $prescription = \App\Models\Prescription::with(['patient', 'doctor', 'consultation'])->findOrFail($id);
            return view('pages.doctor.prescription-print', compact('prescription'));
        })->name('prescriptions.print');
        
        Route::get('/', [PatientController::class, 'index'])->name('index');
    });
});

// Public Queue Monitor
Route::get('/queue-monitor', fn() => view('pages.public.queue'))->name('public.queue');
