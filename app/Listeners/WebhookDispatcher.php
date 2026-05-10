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
        if ($event instanceof PatientRegistered || $event instanceof PatientCreated) {
            $this->service->dispatch('patient.registered', $this->formatPatientData($event->patient));
        }

        if ($event instanceof PatientUpdated) {
            $this->service->dispatch('patient.updated', $this->formatPatientData($event->patient));
        }

        if ($event instanceof PatientDeleted) {
            $this->service->dispatch('patient.deleted', [
                'id' => $event->patient->id,
                'uhid' => $event->patient->uhid,
                'deleted_at' => now()->toIso8601String(),
            ]);
        }

        if ($event instanceof PatientAdmitted) {
            $this->service->dispatch('admission.created', $this->formatAdmissionData($event->admission));
        }

        if ($event instanceof PatientDischarged) {
            $this->service->dispatch('admission.discharged', $this->formatAdmissionData($event->admission));
        }

        if ($event instanceof BillSettled) {
            $this->service->dispatch('invoice.paid', [
                'id' => $event->bill->id,
                'bill_number' => $event->bill->bill_number,
                'patient_name' => $event->bill->patient->full_name,
                'patient_uhid' => $event->bill->patient->uhid,
                'totals' => [
                    'subtotal' => $event->bill->subtotal,
                    'tax' => $event->bill->tax_amount,
                    'discount' => $event->bill->discount_amount,
                    'total' => $event->bill->total_amount,
                ],
                'payment' => [
                    'method' => $event->bill->payment_method,
                    'status' => $event->bill->payment_status,
                ],
                'items' => $event->bill->items->map(fn($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                ])->toArray(),
                'pdf_url' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'billing.bills.pdf',
                    now()->addDays(7),
                    ['bill' => $event->bill->id]
                ),
                'paid_at' => $event->bill->updated_at->toIso8601String(),
            ]);
        }

        if ($event instanceof PaymentReceived) {
            $this->service->dispatch('payment.received', [
                'id' => $event->payment->id,
                'bill' => [
                    'id' => $event->payment->bill_id,
                    'number' => $event->payment->bill?->bill_number,
                    'total' => $event->payment->bill?->total_amount,
                    'balance' => $event->payment->bill?->balance_amount,
                ],
                'patient_name' => $event->payment->bill?->patient?->full_name,
                'amount' => $event->payment->amount,
                'type' => $event->payment->type,
                'method' => $event->payment->method,
                'transaction_id' => $event->payment->transaction_id,
                'received_at' => $event->payment->received_at?->toIso8601String(),
            ]);
        }

        if ($event instanceof ConsultationCreated) {
            $this->service->dispatch('consultation.created', $this->formatConsultationData($event->consultation));
        }

        if ($event instanceof ConsultationCompleted) {
            $data = $this->formatConsultationData($event->consultation);
            $data['completed_at'] = now()->toIso8601String();
            $this->service->dispatch('consultation.completed', $data);
        }

        if ($event instanceof DailySummaryGenerated) {
            $this->service->dispatch('daily.summary', $event->summary);
        }

        if ($event instanceof PrescriptionCreated) {
            $this->service->dispatch('prescription.created', $this->formatPrescriptionData($event->prescription));
        }

        if ($event instanceof PrescriptionDispensed) {
            $data = $this->formatPrescriptionData($event->prescription);
            $data['dispensed_at'] = $event->prescription->dispensed_at?->toIso8601String();
            $this->service->dispatch('prescription.dispensed', $data);
        }

        if ($event instanceof MedicineLowStock) {
            $this->service->dispatch('medicine.low_stock', [
                'id' => $event->medicine->id,
                'name' => $event->medicine->name,
                'stock_quantity' => $event->medicine->stock_quantity,
                'min_stock_level' => $event->medicine->min_stock_level,
            ]);
        }

        if ($event instanceof LabOrderCreated) {
            $this->service->dispatch('lab.order_created', [
                'id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'patient' => [
                    'id' => $event->order->patient_id,
                    'name' => $event->order->patient?->full_name,
                ],
                'test' => [
                    'id' => $event->order->lab_test_id,
                    'name' => $event->order->labTest?->name,
                    'code' => $event->order->labTest?->test_code,
                ],
                'priority' => $event->order->priority,
                'status' => $event->order->status,
                'created_at' => $event->order->created_at?->toIso8601String(),
            ]);
        }

        if ($event instanceof LabOrderCompleted) {
            $this->service->dispatch('lab.order_completed', [
                'id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'patient' => [
                    'id' => $event->order->patient_id,
                    'name' => $event->order->patient?->full_name,
                    'uhid' => $event->order->patient?->uhid,
                ],
                'test' => [
                    'id' => $event->order->lab_test_id,
                    'name' => $event->order->labTest?->name,
                ],
                'results' => $event->order->results,
                'verified_by' => [
                    'id' => $event->order->verified_by,
                    'name' => $event->order->verifiedBy?->name,
                ],
                'completed_at' => $event->order->completed_at?->toIso8601String(),
            ]);
        }
        
        if ($event instanceof AppointmentBooked) {
            $pdfUrl = null;
            if ($event->consultation->bill) {
                $pdfUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'billing.bills.pdf',
                    now()->addDays(7),
                    ['bill' => $event->consultation->bill->id]
                );
            }

            $this->service->dispatch('appointment.booked', [
                'id' => $event->consultation->id,
                'patient' => [
                    'id' => $event->consultation->patient_id,
                    'name' => $event->consultation->patient->full_name,
                    'uhid' => $event->consultation->patient->uhid,
                ],
                'doctor' => [
                    'id' => $event->consultation->doctor_id,
                    'name' => $event->consultation->doctor?->full_name,
                ],
                'visit' => [
                    'token' => $event->consultation->token_number,
                    'type' => $event->consultation->visit_type,
                    'fee' => $event->consultation->fee,
                    'date' => $event->consultation->consultation_date?->toDateString(),
                ],
                'pdf_url' => $pdfUrl,
                'booked_at' => $event->consultation->created_at?->toIso8601String(),
            ]);
        }
    }

    protected function formatPatientData($patient): array
    {
        return [
            'id' => $patient->id,
            'uhid' => $patient->uhid,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'full_name' => $patient->full_name,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'gender' => $patient->gender,
            'age' => $patient->age,
            'date_of_birth' => $patient->date_of_birth?->toDateString(),
            'father_name' => $patient->father_name,
            'mother_name' => $patient->mother_name,
            'address' => $patient->address,
            'city' => $patient->city,
            'state' => $patient->state,
            'pincode' => $patient->pincode,
            'emergency_contact_name' => $patient->emergency_contact_name,
            'emergency_contact_phone' => $patient->emergency_contact_phone,
            'insurance' => [
                'provider' => $patient->insurance_provider,
                'policy' => $patient->insurance_policy,
                'validity' => $patient->insurance_validity?->toDateString(),
            ],
            'created_at' => $patient->created_at->toIso8601String(),
            'updated_at' => $patient->updated_at->toIso8601String(),
        ];
    }

    protected function formatAdmissionData($admission): array
    {
        return [
            'id' => $admission->id,
            'admission_number' => $admission->admission_number,
            'status' => $admission->status,
            'patient' => [
                'id' => $admission->patient_id,
                'uhid' => $admission->patient->uhid,
                'full_name' => $admission->patient->full_name,
            ],
            'doctor' => [
                'id' => $admission->doctor_id,
                'full_name' => $admission->doctor->full_name,
            ],
            'location' => [
                'ward' => $admission->bed?->ward?->name,
                'bed' => $admission->bed?->bed_number,
            ],
            'reason' => $admission->reason_for_admission,
            'guardian' => [
                'name' => $admission->guardian_name,
                'phone' => $admission->guardian_phone,
                'relation' => $admission->guardian_relation,
            ],
            'is_emergency' => $admission->is_emergency,
            'admitted_at' => $admission->admission_date?->toIso8601String(),
            'discharged_at' => $admission->discharge_date?->toIso8601String(),
        ];
    }

    protected function formatConsultationData($consultation): array
    {
        return [
            'id' => $consultation->id,
            'patient' => [
                'id' => $consultation->patient_id,
                'name' => $consultation->patient?->full_name,
            ],
            'doctor' => [
                'id' => $consultation->doctor_id,
                'name' => $consultation->doctor?->full_name,
            ],
            'visit' => [
                'token' => $consultation->token_number,
                'type' => $consultation->visit_type,
                'date' => $consultation->consultation_date?->toDateString(),
                'status' => $consultation->status,
            ],
            'vitals' => [
                'weight' => $consultation->weight,
                'height' => $consultation->height,
                'temperature' => $consultation->temperature,
            ],
            'diagnoses' => $consultation->diagnoses->map(fn($d) => [
                'name' => $d->diagnosis_name,
                'code' => $d->icd_code,
                'type' => $d->type
            ])->toArray(),
            'clinical' => [
                'chief_complaints' => $consultation->chief_complaints,
                'history' => $consultation->history_of_present_illness,
                'advice' => $consultation->advice,
                'follow_up' => $consultation->follow_up_date?->toDateString(),
            ],
            'created_at' => $consultation->created_at?->toIso8601String(),
        ];
    }

    protected function formatPrescriptionData($prescription): array
    {
        return [
            'id' => $prescription->id,
            'patient' => [
                'id' => $prescription->patient_id,
                'name' => $prescription->patient?->full_name,
            ],
            'doctor' => [
                'id' => $prescription->doctor_id,
                'name' => $prescription->doctor?->full_name,
            ],
            'medicines' => $prescription->medicines,
            'created_at' => $prescription->created_at?->toIso8601String(),
        ];
    }
}
