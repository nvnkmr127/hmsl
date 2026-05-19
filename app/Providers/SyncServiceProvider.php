<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillPayment;
use App\Models\BillDiscount;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Ward;
use App\Models\Department;
use App\Models\PatientVital;
use App\Models\InventoryItem;
use App\Models\Service;
use App\Models\Diagnosis;
use App\Models\Consultation;
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
            Doctor::class,
            Appointment::class,
            Bill::class,
            BillItem::class,
            BillPayment::class,
            BillDiscount::class,
            Medicine::class,
            Prescription::class,
            PrescriptionItem::class,
            LabOrder::class,
            LabResult::class,
            Admission::class,
            Bed::class,
            Ward::class,
            Department::class,
            PatientVital::class,
            InventoryItem::class,
            Service::class,
            Diagnosis::class,
            Consultation::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe(SyncOutboxObserver::class);
            }
        }
    }
}
