<?php

namespace App\Listeners;

use App\Events\Patients\PatientRegistered;
use App\Events\IPD\PatientAdmitted;
use App\Events\Billing\BillSettled;
use App\Events\System\DailySummaryGenerated;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WebhookDispatcher
{
    protected $service;

    /**
     * Create the event listener.
     */
    public function __construct(WebhookService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof PatientRegistered) {
            $this->service->dispatch('patient.registered', [
                'id' => $event->patient->id,
                'uhid' => $event->patient->uhid,
                'name' => $event->patient->full_name,
                'phone' => $event->patient->phone,
                'gender' => $event->patient->gender,
                'age' => $event->patient->age,
                'registered_at' => $event->patient->created_at->toIso8601String(),
            ]);
        }

        if ($event instanceof PatientAdmitted) {
            $this->service->dispatch('admission.created', [
                'id' => $event->admission->id,
                'admission_number' => $event->admission->admission_number,
                'patient_name' => $event->admission->patient->full_name,
                'patient_uhid' => $event->admission->patient->uhid,
                'doctor' => $event->admission->doctor->full_name,
                'ward' => $event->admission->bed->ward->name,
                'bed' => $event->admission->bed->bed_number,
                'admitted_at' => $event->admission->admission_date->toIso8601String(),
            ]);
        }

        if ($event instanceof BillSettled) {
            $this->service->dispatch('invoice.paid', [
                'id' => $event->bill->id,
                'bill_number' => $event->bill->bill_number,
                'patient_name' => $event->bill->patient->full_name,
                'amount' => $event->bill->total_amount,
                'method' => $event->bill->payment_method,
                'paid_at' => $event->bill->updated_at->toIso8601String(),
            ]);
        }

        if ($event instanceof \App\Events\OPD\ConsultationCompleted) {
            $this->service->dispatch('consultation.completed', [
                'id' => $event->consultation->id,
                'patient_name' => $event->consultation->patient->full_name,
                'doctor_name' => $event->consultation->doctor->full_name,
                'token' => $event->consultation->token_number,
                'completed_at' => now()->toIso8601String(),
            ]);
        }

        if ($event instanceof DailySummaryGenerated) {
            $this->service->dispatch('daily.summary', $event->summary);
        }
    }
}
