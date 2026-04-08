<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists('\Laravel\Telescope\TelescopeApplicationServiceProvider')) {
            $this->app->register('App\Providers\TelescopeServiceProvider');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Patients\PatientRegistered::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\IPD\PatientAdmitted::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Billing\BillSettled::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Billing\PaymentReceived::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\System\DailySummaryGenerated::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OPD\ConsultationCompleted::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Pharmacy\PrescriptionDispensed::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Pharmacy\MedicineLowStock::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Laboratory\LabOrderCreated::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\Laboratory\LabOrderCompleted::class,
            \App\Listeners\WebhookDispatcher::class
        );
    }
}
