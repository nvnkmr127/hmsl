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
        // Disable SSL certificate verification for HTTP client requests
        \Illuminate\Support\Facades\Http::globalOptions([
            'verify' => false,
        ]);

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

        // Register Sync Observers for local clients
        if (config('sync.token')) {
            $syncModels = [
                \App\Models\Patient::class,
                \App\Models\Doctor::class,
                \App\Models\Appointment::class,
                \App\Models\Bill::class,
                \App\Models\BillItem::class,
                \App\Models\Medicine::class,
                \App\Models\Prescription::class,
                \App\Models\LabOrder::class,
                \App\Models\Admission::class,
                \App\Models\Bed::class,
                \App\Models\Ward::class,
                \App\Models\User::class,
            ];

            foreach ($syncModels as $model) {
                if (class_exists($model)) {
                    $model::observe(\App\Sync\Observers\SyncOutboxObserver::class);
                }
            }
        }

        // Auto-run migrations for SQLite if database file does not exist
        if (config('database.default') === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');
            if ($dbPath && $dbPath !== ':memory:' && !file_exists($dbPath)) {
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                touch($dbPath);
                try {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Auto-migration failed: ' . $e->getMessage());
                }
            }
        }
    }
}
