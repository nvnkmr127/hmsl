<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\PatientApiController;
use App\Http\Controllers\Api\V1\DoctorApiController;
use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\BillApiController;
use App\Http\Controllers\Api\V1\ServiceApiController;
use App\Http\Controllers\Api\V1\DepartmentApiController;
use App\Http\Controllers\Api\V1\PrescriptionApiController;
use App\Http\Controllers\Api\V1\LabApiController;
use App\Http\Controllers\Api\V1\AdmissionApiController;
use App\Http\Controllers\Api\V1\WebhookEndpointApiController;
use App\Http\Controllers\Api\V1\VitalApiController;
use App\Http\Controllers\Api\V1\MedicineApiController;
use App\Http\Controllers\Api\V1\WardApiController;
use App\Http\Controllers\Api\V1\UpdateApiController;

Route::prefix('v1')->group(function () {
    
    // Public Endpoints
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/webhooks/{source}', [WebhookController::class, 'handle']);
    Route::get('/ping', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
            'server' => 'hms-central',
        ]);
    });

    // Authenticated Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('patients', PatientApiController::class);
        Route::apiResource('doctors', DoctorApiController::class)->only(['index', 'show']);
        Route::apiResource('appointments', AppointmentApiController::class);
        Route::apiResource('bills', BillApiController::class)->only(['index', 'show']);
        Route::apiResource('services', ServiceApiController::class)->only(['index', 'show']);
        Route::apiResource('departments', DepartmentApiController::class)->only(['index']);
        Route::apiResource('prescriptions', PrescriptionApiController::class)->only(['index', 'show']);
        Route::apiResource('lab-results', LabApiController::class)->only(['index', 'show']);
        Route::apiResource('admissions', AdmissionApiController::class)->only(['index', 'show']);
        Route::get('webhook-endpoints/events', [WebhookEndpointApiController::class, 'events']);
        Route::post('webhook-endpoints/{webhook_endpoint}/rotate-secret', [WebhookEndpointApiController::class, 'rotateSecret']);
        Route::get('webhook-endpoints/{webhook_endpoint}/logs', [WebhookEndpointApiController::class, 'logs']);
        Route::post('webhook-endpoints/{webhook_endpoint}/test', [WebhookEndpointApiController::class, 'test']);
        Route::apiResource('webhook-endpoints', WebhookEndpointApiController::class);
        Route::apiResource('vitals', VitalApiController::class)->only(['index', 'show']);
        Route::apiResource('medicines', MedicineApiController::class)->only(['index', 'show']);
        Route::apiResource('wards', WardApiController::class)->only(['index']);

        // Code/UI Update Endpoints (used by offline desktop clients)
        Route::get('update/check', [UpdateApiController::class, 'check']);
        Route::get('update/manifest', [UpdateApiController::class, 'manifest']);
        Route::get('update/download', [UpdateApiController::class, 'download']);
    });

    // Synchronization Endpoints (Used by local instances to sync with this server)
    Route::group(['prefix' => 'sync', 'middleware' => ['auth:sanctum', 'sync.device']], function () {
        Route::post('/push', [\App\Sync\API\Controllers\SyncController::class, 'push']);
        Route::get('/pull', [\App\Sync\API\Controllers\SyncController::class, 'pull']);
        Route::get('/status', [\App\Sync\API\Controllers\SyncController::class, 'status']);
    });
    
    // Device Registration (Public or restricted by other means)
    Route::post('/sync/register', [\App\Sync\API\Controllers\SyncController::class, 'registerDevice']);
});
