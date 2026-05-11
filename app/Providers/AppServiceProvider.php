<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        $events = [
            \App\Events\Patients\PatientRegistered::class,
            \App\Events\Patients\PatientUpdated::class,
            \App\Events\Patients\PatientDeleted::class,
            \App\Events\PatientCreated::class,
            \App\Events\IPD\PatientAdmitted::class,
            \App\Events\IPD\PatientDischarged::class,
            \App\Events\Billing\BillSettled::class,
            \App\Events\Billing\PaymentReceived::class,
            \App\Events\System\DailySummaryGenerated::class,
            \App\Events\System\CronJobFailed::class,
            \App\Events\OPD\ConsultationCreated::class,
            \App\Events\OPD\ConsultationCompleted::class,
            \App\Events\Pharmacy\PrescriptionCreated::class,
            \App\Events\Pharmacy\PrescriptionDispensed::class,
            \App\Events\Pharmacy\MedicineLowStock::class,
            \App\Events\Laboratory\LabOrderCreated::class,
            \App\Events\Laboratory\LabOrderCompleted::class,
            \App\Events\OPD\AppointmentBooked::class,
        ];

        foreach ($events as $event) {
            \Illuminate\Support\Facades\Event::listen($event, \App\Listeners\WebhookDispatcher::class);
        }

        Livewire::component('ipd.transfer-bed', \App\Livewire\Ipd\TransferBed::class);

        Gate::define('viewLogViewer', function ($user) {
            return $user->can('manage settings');
        });
    }
}
