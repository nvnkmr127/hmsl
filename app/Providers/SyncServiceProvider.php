<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Medicine;
use App\Models\LabOrder;
use App\Models\Prescription;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\InventoryItem;
use App\Sync\Observers\SyncOutboxObserver;
use Illuminate\Support\ServiceProvider;

class SyncServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Attach observers to core models
        $models = [
            Patient::class,
            Appointment::class,
            Bill::class,
            Medicine::class,
            LabOrder::class,
            Prescription::class,
            Admission::class,
            Bed::class,
            InventoryItem::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe(SyncOutboxObserver::class);
            }
        }
    }
}
