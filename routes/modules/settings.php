<?php

use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix' => 'settings', 'as' => 'settings.', 'middleware' => ['permission:manage settings', 'hospital_owner']], function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/preferences', [SettingsController::class, 'preferences'])->name('preferences');
        Route::get('/invoice', [SettingsController::class, 'invoice'])->name('invoice');
        
        // Webhooks
        Route::get('/webhooks', fn() => view('pages.settings.webhooks'))->name('webhooks.index');
        Route::get('/webhooks/logs', fn() => view('pages.settings.webhook-logs'))->name('webhooks.logs');
        Route::get('/webhooks/inbound', fn() => view('pages.settings.inbound-webhooks'))->name('webhooks.inbound');
        Route::get('/api-tokens', fn() => view('pages.settings.api-tokens'))->name('api-tokens.index');
    });
});
