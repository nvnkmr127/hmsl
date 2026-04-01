<?php

use App\Http\Controllers\Counter\PatientController;
use App\Http\Controllers\Counter\OpdController;
use App\Http\Controllers\Counter\AddressAutocompleteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'counter', 'as' => 'counter.'], function () {
        // Patients
        Route::group(['middleware' => ['permission:view patients']], function() {
            Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
            Route::get('/patients/{id}/history', function($id) {
                return view('pages.counter.history', ['id' => $id]);
            })->name('patients.history');
            Route::get('/address-autocomplete', AddressAutocompleteController::class)->name('address.autocomplete')->middleware('throttle:30,1');
        });
        
        // OPD
        Route::get('/opd', [OpdController::class, 'index'])->name('opd.index')->middleware('permission:view opd');
        Route::get('/opd/{id}/print', [OpdController::class, 'print'])->name('opd.print')->middleware('permission:view opd');
        
        // IPD Routes
        Route::group(['middleware' => ['permission:view ipd']], function() {
            Route::get('/ipd', fn() => view('pages.ipd.index'))->name('ipd.index');
            Route::get('/ipd/create', fn() => view('pages.ipd.create'))->name('ipd.create');
        });

        // Billing
        Route::group(['middleware' => ['permission:view billing']], function() {
            Route::get('/billing', fn() => view('pages.billing.index'))->name('billing.index');
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
