<?php

namespace App\Listeners;

use App\Events\Patients\PatientRegistered;
use App\Events\Patients\PatientUpdated;
use App\Events\Patients\PatientDeleted;
use App\Events\PatientCreated;
use App\Events\IPD\PatientAdmitted;
use App\Events\IPD\PatientDischarged;
use App\Events\Billing\BillSettled;
use App\Events\Billing\PaymentReceived;
use App\Events\System\DailySummaryGenerated;
use App\Events\OPD\ConsultationCompleted;
use App\Events\OPD\ConsultationCreated;
use App\Events\OPD\AppointmentBooked;
use App\Events\Pharmacy\PrescriptionDispensed;
use App\Events\Pharmacy\PrescriptionCreated;
use App\Events\Pharmacy\MedicineLowStock;
use App\Events\Laboratory\LabOrderCreated;
use App\Events\Laboratory\LabOrderCompleted;
use App\Services\WebhookService;
use App\Services\Webhooks\Factories\PatientPayloadFactory;
use App\Services\Webhooks\Factories\OpdPayloadFactory;
use App\Services\Webhooks\Factories\BillingPayloadFactory;
use App\Services\Webhooks\Factories\LabPayloadFactory;
use App\Services\Webhooks\Factories\PharmacyPayloadFactory;
use App\Services\Webhooks\Factories\IpdPayloadFactory;

class WebhookDispatcher
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected WebhookService $service
    ) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        // Patient Events
        if ($event instanceof PatientRegistered || $event instanceof PatientCreated) {
            $this->service->dispatch('patient.registered', PatientPayloadFactory::forPatient($event->patient));
        }

        if ($event instanceof PatientUpdated) {
            $this->service->dispatch('patient.updated', PatientPayloadFactory::forPatient($event->patient));
        }

        if ($event instanceof PatientDeleted) {
            $this->service->dispatch('patient.deleted', [
                'id' => $event->patient->id,
                'uhid' => $event->patient->uhid,
                'deleted_at' => now()->toIso8601String(),
            ]);
        }

        // OPD Events
        if ($event instanceof AppointmentBooked) {
            $this->service->dispatch('appointment.booked', OpdPayloadFactory::forConsultation($event->consultation));
        }

        if ($event instanceof ConsultationCreated) {
            $this->service->dispatch('consultation.created', OpdPayloadFactory::forConsultation($event->consultation));
        }

        if ($event instanceof ConsultationCompleted) {
            $this->service->dispatch('consultation.completed', OpdPayloadFactory::forConsultation($event->consultation));
        }

        // Billing Events
        if ($event instanceof BillSettled) {
            $this->service->dispatch('bill.paid', BillingPayloadFactory::forBill($event->bill));
        }

        // Lab Events
        if ($event instanceof LabOrderCreated) {
            $this->service->dispatch('lab.order.completed', LabPayloadFactory::forOrder($event->order));
        }

        if ($event instanceof LabOrderCompleted) {
            $this->service->dispatch('lab.order.completed', LabPayloadFactory::forOrder($event->order));
        }

        // Pharmacy Events
        if ($event instanceof PrescriptionCreated) {
            $this->service->dispatch('pharmacy.prescription.created', PharmacyPayloadFactory::forPrescription($event->prescription));
        }

        // IPD Events
        if ($event instanceof PatientAdmitted) {
            $this->service->dispatch('ipd.admission.created', IpdPayloadFactory::forAdmission($event->admission));
        }

        // System Events
        if ($event instanceof DailySummaryGenerated) {
            $this->service->dispatch('system.daily.summary', $event->summary);
        }
    }
}
