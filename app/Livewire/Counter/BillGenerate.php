<?php

namespace App\Livewire\Counter;

use App\Models\Consultation;
use App\Models\Bill;
use App\Models\Patient;
use App\Services\BillingService;
use Livewire\Component;
use Livewire\Attributes\On;

class BillGenerate extends Component
{
    public $consultationId;
    public $patientId;
    public $patientName;
    public $items = [];
    public $discount = 0;
    public $tax = 0;
    public $paymentStatus = 'Paid';
    public $amountPaid = 0;
    public $paymentMethod = 'Cash';
    public $notes;
    public $discountType = 'flat';
    public $discountReason = '';
    public $isAuthorizedByDoctor = false;
    public $authorizedLimit = 0;

    #[On('generate-bill')]
    public function openBillingModal($data = null)
    {
        if (!$data) return;
        $this->consultationId = is_array($data) ? ($data['consultationId'] ?? null) : $data;
        $consultation = Consultation::with('patient', 'doctor')->findOrFail($this->consultationId);

        $this->patientId = $consultation->patient_id;
        $this->patientName = $consultation->patient->full_name;
        
        $doctorName = $consultation->doctor ? ' - ' . $consultation->doctor->full_name : '';
        $this->items = [
            [
                'service_key' => 'Service:' . $consultation->service_id,
                'name' => ($consultation->service?->name ?? 'Consultation Fee') . $doctorName,
                'type' => 'Consultation',
                'quantity' => 1,
                'unit_price' => $consultation->fee,
                'is_preset' => true
            ]
        ];

        $this->discount = $consultation->discount_amount ?? 0;
        $this->isAuthorizedByDoctor = $consultation->is_discount_authorized;
        $this->authorizedLimit = $consultation->authorized_discount_limit;
        
        $this->discountType = 'flat';
        $this->discountReason = $this->discount > 0 ? 'Doctor consultation discount' : '';
        $this->tax = 0;
        $this->paymentStatus = $consultation->payment_status === 'Paid' ? 'Paid' : 'Unpaid';
        $this->paymentMethod = 'Cash';
        $this->amountPaid = $this->paymentStatus === 'Paid' ? (float) $this->total : 0;
        $this->notes = '';

        $this->dispatch('open-modal', name: 'billing-modal');
    }

    #[On('generate-bill-for-patient')]
    public function openBillingModalForPatient($data = null)
    {
        if (!$data) return;
        $this->consultationId = null;
        $this->patientId = is_array($data) ? ($data['patientId'] ?? null) : $data;
        $patient = Patient::findOrFail($this->patientId);

        $this->patientId = $patient->id;
        $this->patientName = $patient->full_name;

        $this->items = [
            [
                'service_key' => '',
                'name' => '',
                'type' => '',
                'quantity' => 1,
                'unit_price' => 0,
                'is_preset' => false
            ],
        ];
        $this->discount = 0;
        $this->discountType = 'flat';
        $this->discountReason = '';
        $this->tax = 0;
        $this->paymentStatus = 'Paid';
        $this->paymentMethod = 'Cash';
        $this->amountPaid = (float) $this->total;
        $this->notes = '';

        $this->dispatch('open-modal', name: 'billing-modal');
    }

    public function getMasterServicesProperty()
    {
        $services = \App\Models\Service::where('is_active', true)->get()->map(function($s) {
            return [
                'key' => 'Service:' . $s->id,
                'name' => $s->name,
                'price' => (float)$s->price,
                'type' => 'Service',
                'label' => $s->name . ' (OPD - ₹' . number_format($s->price) . ')'
            ];
        });

        $ipServices = \App\Models\IpService::where('is_active', true)->get()->map(function($s) {
            return [
                'key' => 'IpService:' . $s->id,
                'name' => $s->name,
                'price' => (float)$s->price,
                'type' => 'IP Service',
                'label' => $s->name . ' (IPD - ₹' . number_format($s->price) . ')'
            ];
        });

        $labTests = \App\Models\LabTest::where('is_active', true)->get()->map(function($s) {
            return [
                'key' => 'LabTest:' . $s->id,
                'name' => $s->name,
                'price' => (float)$s->price,
                'type' => 'Lab',
                'label' => $s->name . ' (Lab - ₹' . number_format($s->price) . ')'
            ];
        });

        return $services->concat($ipServices)->concat($labTests)->toArray();
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = $parts[0];
            $field = $parts[1];

            if ($field === 'service_key' && !empty($value)) {
                list($type, $id) = explode(':', $value);
                
                $name = '';
                $price = 0;
                $itemType = 'Other';

                if ($type === 'Service') {
                    $model = \App\Models\Service::find($id);
                    if ($model) {
                        $name = $model->name;
                        $price = $model->price;
                        $itemType = 'Service';
                    }
                } elseif ($type === 'IpService') {
                    $model = \App\Models\IpService::find($id);
                    if ($model) {
                        $name = $model->name;
                        $price = $model->price;
                        $itemType = 'IP Service';
                    }
                } elseif ($type === 'LabTest') {
                    $model = \App\Models\LabTest::find($id);
                    if ($model) {
                        $name = $model->name;
                        $price = $model->price;
                        $itemType = 'Lab';
                    }
                }

                $this->items[$index]['name'] = $name;
                $this->items[$index]['type'] = $itemType;
                $this->items[$index]['unit_price'] = (float) $price;
            }
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'service_key' => '',
            'name' => '',
            'type' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'is_preset' => false
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(function($item) {
            return $item['quantity'] * $item['unit_price'];
        });
    }

    public function getTotalProperty()
    {
        $discountAmount = $this->discount;
        if ($this->discountType === 'percentage') {
            $discountAmount = ($this->subtotal * $this->discount) / 100;
        }
        return $this->subtotal - $discountAmount + $this->tax;
    }

    public function save(BillingService $service)
    {
        $this->validate([
            'paymentStatus' => 'required|in:Paid,Unpaid,Partially Paid,Partial',
            'amountPaid' => 'nullable|numeric|min:0',
            'paymentMethod' => 'nullable|string|max:50',
            'discount' => 'numeric|min:0',
            'discountReason' => 'nullable|required_if:discount,>0|string|max:255',
            'items.*.service_key' => 'required_without:items.*.is_preset',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        foreach ($this->items as $item) {
            if (!empty($item['is_preset'])) {
                continue;
            }
            if (empty($item['service_key'])) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Please select a service for all items.']);
                return;
            }
            list($type, $id) = explode(':', $item['service_key']);
            
            $dbPrice = null;
            $dbName = '';

            if ($type === 'Service') {
                $model = \App\Models\Service::find($id);
                if ($model) {
                    $dbPrice = (float) $model->price;
                    $dbName = $model->name;
                }
            } elseif ($type === 'IpService') {
                $model = \App\Models\IpService::find($id);
                if ($model) {
                    $dbPrice = (float) $model->price;
                    $dbName = $model->name;
                }
            } elseif ($type === 'LabTest') {
                $model = \App\Models\LabTest::find($id);
                if ($model) {
                    $dbPrice = (float) $model->price;
                    $dbName = $model->name;
                }
            }

            if ($dbPrice === null) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Invalid service selected.']);
                return;
            }

            if ((float)$item['unit_price'] !== $dbPrice || $item['name'] !== $dbName) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Service pricing discrepancy detected. Please try again.']);
                return;
            }
        }

        try {
            $bill = $service->createBill([
                'patient_id' => $this->patientId,
                'consultation_id' => $this->consultationId,
                'discount_amount' => 0, // We will apply it separately for auditing
                'tax_amount' => $this->tax,
                'payment_status' => $this->paymentStatus,
                'paid_amount' => (float) $this->amountPaid,
                'payment_method' => $this->paymentMethod,
                'notes' => $this->notes,
            ], $this->items);

            if ($this->discount > 0) {
                $service->applyDiscount($bill, [
                    'type' => $this->discountType,
                    'value' => $this->discount,
                    'reason' => $this->discountReason ?: 'Initial bill discount',
                ]);
            }

            $this->dispatch('close-modal', name: 'billing-modal');
            $this->dispatch('bill-generated', billId: $bill->id);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Bill generated successfully!']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.counter.bill-generate');
    }
}
