<?php

namespace App\Livewire\Counter;

use App\Models\Admission;
use App\Models\LabTest;
use App\Services\LabOrderService;
use App\Services\IpdService;
use Livewire\Component;
use Livewire\WithPagination;

class IpdAdmissions extends Component
{
    use WithPagination;

    #[\Livewire\Attributes\On('refresh-admissions')]
    public function refreshList()
    {
        // Re-renders the component to fetch updated data
    }

    public $search = '';
    public string $dateFilterType = 'admission';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public $admissionStatus = '';
    public $billingStatus = '';
    public $wardFilter = '';
    public $doctorFilter = '';
    public ?int $selectedAdmissionId = null;
    public string $dischargeNotes = '';
    public string $dischargeError = '';
    public ?int $selectedLabAdmissionId = null;
    public array $selectedLabTests = [];
    public ?string $labNotes = null;
    
    public ?int $selectedTransferAdmissionId = null;
    public ?int $selectedTransferBedId = null;
    public string $transferNotes = '';

    public function dischargePatient($id)
    {
        $this->selectedAdmissionId = (int) $id;
        $this->dischargeNotes = '';
        $this->dischargeError = '';
        $this->dispatch('open-modal', name: 'ipd-discharge-modal');
    }

    public function initiateTransfer($id)
    {
        $this->selectedTransferAdmissionId = (int) $id;
        $this->selectedTransferBedId = null;
        $this->transferNotes = '';
        $this->dispatch('open-modal', name: 'ipd-transfer-modal');
    }

    public function orderLabs($id): void
    {
        $this->selectedLabAdmissionId = (int) $id;
        $this->selectedLabTests = [];
        $this->labNotes = null;
        $this->dispatch('open-modal', name: 'ipd-lab-order-modal');
    }

    public function confirmLabOrder(LabOrderService $service): void
    {
        if (!$this->selectedLabAdmissionId) {
            return;
        }

        $this->validate([
            'selectedLabAdmissionId' => 'required|integer|exists:admissions,id',
            'selectedLabTests' => 'required|array|min:1',
            'selectedLabTests.*' => 'integer|exists:lab_tests,id',
            'labNotes' => 'nullable|string|max:2000',
        ]);

        $admission = Admission::with(['patient', 'doctor'])->findOrFail($this->selectedLabAdmissionId);

        $created = $service->createOrders([
            'patient_id' => (int) $admission->patient_id,
            'doctor_id' => (int) $admission->doctor_id,
            'admission_id' => (int) $admission->id,
            'notes' => $this->labNotes,
        ], $this->selectedLabTests);

        if (count($created) === 0) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'No lab orders were created.']);
            return;
        }

        $this->dispatch('close-modal', name: 'ipd-lab-order-modal');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Lab order created.']);
        $this->reset(['selectedLabAdmissionId', 'selectedLabTests', 'labNotes']);
    }

    public function confirmDischarge(IpdService $manager)
    {
        if (!$this->selectedAdmissionId) {
            return;
        }

        $this->dischargeError = '';

        try {
            $admission = Admission::findOrFail($this->selectedAdmissionId);
            $manager->dischargePatient($admission, $this->dischargeNotes ?: null);

            $this->dispatch('close-modal', name: 'ipd-discharge-modal');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Patient discharged successfully!']);
            $this->reset(['selectedAdmissionId', 'dischargeNotes', 'dischargeError']);
        } catch (\Exception $e) {
            $this->dischargeError = $e->getMessage();
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function confirmTransfer(IpdService $manager)
    {
        $this->validate([
            'selectedTransferAdmissionId' => 'required|integer|exists:admissions,id',
            'selectedTransferBedId' => 'required|integer|exists:beds,id',
            'transferNotes' => 'nullable|string|max:1000',
        ]);

        $admission = Admission::findOrFail($this->selectedTransferAdmissionId);
        
        try {
            $manager->transferPatient($admission, $this->selectedTransferBedId, $this->transferNotes ?: null);
            
            $this->dispatch('close-modal', name: 'ipd-transfer-modal');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Patient transferred successfully!']);
            $this->reset(['selectedTransferAdmissionId', 'selectedTransferBedId', 'transferNotes']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function resetFilters()
    {
        $this->reset(['dateFilterType', 'dateFrom', 'dateTo']);
    }

    public function render()
    {
        $baseAdmissionQuery = Admission::query()
            ->when($this->dateFrom, function ($q) {
                if ($this->dateFilterType === 'billing') {
                    $q->whereHas('finalBill', function ($bq) {
                        $bq->whereDate('created_at', '>=', $this->dateFrom);
                    });
                } else {
                    $field = $this->dateFilterType === 'discharge' ? 'discharge_date' : 'admission_date';
                    $q->whereDate($field, '>=', $this->dateFrom);
                }
            })
            ->when($this->dateTo, function ($q) {
                if ($this->dateFilterType === 'billing') {
                    $q->whereHas('finalBill', function ($bq) {
                        $bq->whereDate('created_at', '<=', $this->dateTo);
                    });
                } else {
                    $field = $this->dateFilterType === 'discharge' ? 'discharge_date' : 'admission_date';
                    $q->whereDate($field, '<=', $this->dateTo);
                }
            });

        $admissions = (clone $baseAdmissionQuery)->with(['patient', 'bed', 'bed.ward', 'doctor.user', 'finalBill'])
            ->when($this->admissionStatus, function ($q) {
                $q->where('status', $this->admissionStatus);
            })
            ->when($this->wardFilter, function ($q) {
                $q->whereHas('bed', function ($bq) {
                    $bq->where('ward_id', $this->wardFilter);
                });
            })
            ->when($this->doctorFilter, function ($q) {
                $q->where('doctor_id', $this->doctorFilter);
            })
            ->when($this->billingStatus, function ($q) {
                if ($this->billingStatus === 'Unpaid') {
                    $q->whereHas('finalBill', function ($bq) {
                        $bq->where('payment_status', 'Unpaid');
                    })->orWhereDoesntHave('finalBill');
                } else {
                    $q->whereHas('finalBill', function ($bq) {
                        $bq->where('payment_status', $this->billingStatus);
                    });
                }
            })
            ->when($this->search, function ($query) {
                $term = "%{$this->search}%";
                $query->where(function ($q) use ($term) {
                    $q->where('admission_number', 'like', $term)
                        ->orWhereHas('patient', function ($pq) use ($term) {
                            $pq->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhere('uhid', 'like', $term);
                        });
                });
            })
            ->latest('admission_date')
            ->paginate(10);

        $baseBillQuery = \App\Models\Bill::whereNotNull('admission_id')
            ->when($this->dateFrom, function ($q) {
                if ($this->dateFilterType === 'billing') {
                    $q->whereDate('created_at', '>=', $this->dateFrom);
                } else {
                    $field = $this->dateFilterType === 'discharge' ? 'discharge_date' : 'admission_date';
                    $q->whereHas('admission', function ($aq) use ($field) {
                        $aq->whereDate($field, '>=', $this->dateFrom);
                    });
                }
            })
            ->when($this->dateTo, function ($q) {
                if ($this->dateFilterType === 'billing') {
                    $q->whereDate('created_at', '<=', $this->dateTo);
                } else {
                    $field = $this->dateFilterType === 'discharge' ? 'discharge_date' : 'admission_date';
                    $q->whereHas('admission', function ($aq) use ($field) {
                        $aq->whereDate($field, '<=', $this->dateTo);
                    });
                }
            });

        $stats = [
            'total' => (clone $baseAdmissionQuery)->count(),
            'admitted' => (clone $baseAdmissionQuery)->where('status', 'Admitted')->count(),
            'discharged' => (clone $baseAdmissionQuery)->where('status', 'Discharged')->count(),
            'total_billed' => (clone $baseBillQuery)->sum('total_amount'),
            'collections' => (clone $baseBillQuery)->sum('paid_amount'),
            'due' => (clone $baseBillQuery)->sum('balance_amount'),
        ];

        return view('livewire.counter.ipd-admissions', [
            'admissions' => $admissions,
            'stats' => $stats,
            'dischargeTemplates' => \App\Models\ClinicalTemplate::where('type', 'discharge')->get(),
            'labTests' => LabTest::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']),
            'wards' => \App\Models\Ward::with(['beds' => function($q) {
                $q->where('is_available', true)->orderBy('bed_number');
            }])->where('is_active', true)->orderBy('name')->get(),
            'doctors' => \App\Models\Doctor::with('user')->where('is_active', true)->get(),
        ]);
    }
}
