<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\PatientApiController;
use App\Http\Controllers\Api\V1\DoctorApiController;
use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\WebhookController;

Route::prefix('v1')->group(function () {
    
    // Public/Shared Endpoints
    Route::post('/webhooks/{source}', [WebhookController::class, 'handle']);

    // Authenticated Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('patients', PatientApiController::class);
        Route::apiResource('doctors', DoctorApiController::class)->only(['index', 'show']);
        Route::apiResource('appointments', AppointmentApiController::class);
    });
});
