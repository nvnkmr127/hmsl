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
        if ($this->app->environment('local')) {
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);
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
            \App\Events\System\DailySummaryGenerated::class,
            \App\Listeners\WebhookDispatcher::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\OPD\ConsultationCompleted::class,
            \App\Listeners\WebhookDispatcher::class
        );
    }
}
