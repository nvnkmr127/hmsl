<?php

namespace App\Livewire\Counter;

use App\Models\Admission;
use App\Models\LabOrder;
use App\Models\PatientVaccination;
use App\Models\PatientConsent;
use App\Models\Prescription;
use App\Models\Vaccine;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\PatientVital;
use App\Models\Bill;
use App\Models\BillPayment;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientHistory extends Component
{
    use WithPagination;
    use WithFileUploads;
    public $patientId;
    public $patient;
    public string $tab = 'overview';
    public string $search = '';
    public ?string $status = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public int $perPage = 10;

    public $consentFile;
    public string $consentType = 'high_risk';
    public ?string $consentSignedAt = null;
    public ?string $consentNotes = null;

    protected $queryString = [
        'tab' => ['except' => 'overview'],
        'search' => ['except' => ''],
        'status' => ['except' => null],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
    ];

    public function mount($id)
    {
        $this->patientId = $id;
        $this->patient = Patient::findOrFail($id);
    }

    #[On('patient-saved')]
    public function refreshPatient(): void
    {
        $this->patient = Patient::findOrFail($this->patientId);
    }

    public function updatedTab(): void
    {
        $this->resetPage();
        $this->reset(['search', 'status', 'dateFrom', 'dateTo']);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function uploadConsent(): void
    {
        $this->validate([
            'consentType' => 'required|string|max:50',
            'consentSignedAt' => 'nullable|date',
            'consentNotes' => 'nullable|string|max:2000',
            'consentFile' => 'required|file|max:8192|mimetypes:application/pdf,image/jpeg,image/png,image/webp',
        ]);

        $path = $this->consentFile->store("patients/{$this->patientId}/consents", 'public');

        PatientConsent::create([
            'patient_id' => $this->patientId,
            'type' => $this->consentType,
            'file_path' => $path,
            'original_name' => $this->consentFile->getClientOriginalName(),
            'mime_type' => $this->consentFile->getMimeType(),
            'signed_at' => $this->consentSignedAt ?: null,
            'notes' => $this->consentNotes,
            'created_by' => Auth::id(),
        ]);

        Cache::forget("patient_counts_{$this->patientId}");
        $this->reset(['consentFile', 'consentSignedAt', 'consentNotes']);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Consent uploaded successfully.']);
    }

    public function deleteConsent(int $consentId): void
    {
        $consent = PatientConsent::where('patient_id', $this->patientId)->findOrFail($consentId);

        if ($consent->file_path) {
            Storage::disk('public')->delete($consent->file_path);
        }

        $consent->delete();

        Cache::forget("patient_counts_{$this->patientId}");
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Consent deleted.']);
    }

    private function applyDateFilter($query, string $column)
    {
        if ($this->dateFrom) {
            $query->whereDate($column, '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate($column, '<=', $this->dateTo);
        }

        return $query;
    }

    private function exportCsv(string $filename, array $headers, $rows): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function export(string $type)
    {
        $id = $this->patientId;
        $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $this->patient->uhid ?: ('patient_' . $id));
        $date = now()->format('Ymd_His');

        if ($type === 'bills') {
            $query = Bill::with(['patient'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            if ($this->status) {
                $query->where('payment_status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('bill_number', 'like', $term)->orWhere('notes', 'like', $term);
                });
            }

            $bills = $query->get();
            $rows = $bills->map(function (Bill $b) {
                return [
                    $b->bill_number,
                    $b->created_at?->format('Y-m-d H:i'),
                    $b->total_amount,
                    $b->payment_status,
                    $b->payment_method,
                ];
            });

            return $this->exportCsv("{$safeName}_billing_{$date}.csv", ['Bill No', 'Date', 'Total', 'Payment Status', 'Payment Method'], $rows);
        }

        if ($type === 'vitals') {
            $query = PatientVital::with(['recorder'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            
            $vitals = $query->get();
            $rows = $vitals->map(function (PatientVital $v) {
                return [
                    $v->created_at?->format('Y-m-d H:i'),
                    $v->temperature,
                    $v->weight,
                    $v->bp_systolic . ($v->bp_diastolic ? '/' . $v->bp_diastolic : ''),
                    $v->spo2,
                    $v->recorder?->name
                ];
            });

            return $this->exportCsv("{$safeName}_vitals_{$date}.csv", ['Date/Time', 'Temp', 'Weight', 'BP', 'Spo2', 'Staff'], $rows);
        }

        if ($type === 'visits') {
            $query = Consultation::with(['doctor.department'])->where('patient_id', $id)->orderByDesc('consultation_date');
            $query = $this->applyDateFilter($query, 'consultation_date');
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('token_number', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('doctor', function ($dq) use ($term) {
                            $dq->where('full_name', 'like', $term);
                        });
                });
            }

            $visits = $query->get();
            $rows = $visits->map(function (Consultation $v) {
                return [
                    $v->token_number,
                    $v->consultation_date?->format('Y-m-d'),
                    $v->doctor?->full_name,
                    $v->status,
                    $v->payment_status,
                    $v->payment_method,
                ];
            });

            return $this->exportCsv("{$safeName}_op_visits_{$date}.csv", ['Token', 'Date', 'Doctor', 'Status', 'Payment Status', 'Payment Method'], $rows);
        }

        if ($type === 'admissions') {
            $query = Admission::with(['bed.ward', 'doctor'])->where('patient_id', $id)->orderByDesc('admission_date');
            $query = $this->applyDateFilter($query, 'admission_date');
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('admission_number', 'like', $term)
                        ->orWhere('reason_for_admission', 'like', $term)
                        ->orWhereHas('doctor', function ($dq) use ($term) {
                            $dq->where('full_name', 'like', $term);
                        })
                        ->orWhereHas('bed.ward', function ($wq) use ($term) {
                            $wq->where('name', 'like', $term);
                        });
                });
            }

            $admissions = $query->get();
            $rows = $admissions->map(function (Admission $a) {
                return [
                    $a->admission_number,
                    $a->admission_date?->format('Y-m-d H:i'),
                    $a->discharge_date?->format('Y-m-d H:i'),
                    $a->status,
                    $a->bed?->ward?->name,
                    $a->bed?->bed_number,
                    $a->doctor?->full_name,
                ];
            });

            return $this->exportCsv("{$safeName}_ipd_admissions_{$date}.csv", ['Admission No', 'Admitted', 'Discharged', 'Status', 'Ward', 'Bed', 'Doctor'], $rows);
        }

        if ($type === 'prescriptions') {
            $query = Prescription::with(['doctor'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('diagnosis', 'like', $term)
                        ->orWhere('chief_complaint', 'like', $term)
                        ->orWhereHas('doctor', function ($dq) use ($term) {
                            $dq->where('full_name', 'like', $term);
                        });
                });
            }

            $rxs = $query->get();
            $rows = $rxs->map(function (Prescription $p) {
                return [
                    $p->created_at?->format('Y-m-d H:i'),
                    $p->doctor?->full_name,
                    $p->diagnosis,
                    $p->follow_up_date?->format('Y-m-d'),
                    is_array($p->medicines) ? count($p->medicines) : 0,
                ];
            });

            return $this->exportCsv("{$safeName}_prescriptions_{$date}.csv", ['Date', 'Doctor', 'Diagnosis', 'Follow Up', 'Medicine Count'], $rows);
        }

        if ($type === 'labs') {
            $query = LabOrder::with(['labTest', 'doctor'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('labTest', function ($tq) use ($term) {
                        $tq->where('name', 'like', $term);
                    })->orWhereHas('doctor', function ($dq) use ($term) {
                        $dq->where('full_name', 'like', $term);
                    });
                });
            }

            $orders = $query->get();
            $rows = $orders->map(function (LabOrder $o) {
                return [
                    $o->created_at?->format('Y-m-d H:i'),
                    $o->labTest?->name,
                    $o->status,
                    $o->collected_at ? \Illuminate\Support\Carbon::parse($o->collected_at)->format('Y-m-d H:i') : null,
                    $o->completed_at ? \Illuminate\Support\Carbon::parse($o->completed_at)->format('Y-m-d H:i') : null,
                ];
            });

            return $this->exportCsv("{$safeName}_lab_orders_{$date}.csv", ['Date', 'Test', 'Status', 'Collected At', 'Completed At'], $rows);
        }

        if ($type === 'payments') {
            $query = BillPayment::query()
                ->whereHas('bill', fn ($q) => $q->where('patient_id', $id))
                ->with(['bill'])
                ->orderByDesc('received_at');

            $query = $this->applyDateFilter($query, 'received_at');
            if ($this->status) {
                $query->where('type', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->whereHas('bill', fn ($bq) => $bq->where('bill_number', 'like', $term));
            }

            $payments = $query->get();
            $rows = $payments->map(function (BillPayment $p) {
                return [
                    $p->received_at?->format('Y-m-d H:i'),
                    $p->bill?->bill_number,
                    $p->type,
                    $p->amount,
                    $p->method,
                    $p->reference,
                ];
            });

            return $this->exportCsv("{$safeName}_payments_{$date}.csv", ['Date', 'Bill No', 'Type', 'Amount', 'Method', 'Reference'], $rows);
        }

        if ($type === 'treatment') {
            $visits = Consultation::with(['doctor'])->where('patient_id', $id)->orderByDesc('consultation_date')->get();
            $admissions = Admission::with(['doctor', 'bed.ward'])->where('patient_id', $id)->orderByDesc('admission_date')->get();
            $prescriptions = Prescription::with(['doctor'])->where('patient_id', $id)->orderByDesc('created_at')->get();

            $rows = collect();

            foreach ($visits as $v) {
                $rows->push([
                    $v->consultation_date?->format('Y-m-d'),
                    'OP Visit',
                    $v->doctor?->full_name,
                    $v->status,
                    $v->notes,
                ]);
            }
            foreach ($admissions as $a) {
                $rows->push([
                    $a->admission_date?->format('Y-m-d H:i'),
                    'IPD',
                    $a->doctor?->full_name,
                    $a->status,
                    $a->notes ?: $a->reason_for_admission,
                ]);
            }
            foreach ($prescriptions as $p) {
                $rows->push([
                    $p->created_at?->format('Y-m-d H:i'),
                    'Prescription',
                    $p->doctor?->full_name,
                    $p->diagnosis,
                    $p->advice,
                ]);
            }

            $rows = $rows->filter(fn ($r) => array_filter($r, fn ($v) => !is_null($v) && $v !== '') !== [])->values();

            return $this->exportCsv("{$safeName}_treatment_history_{$date}.csv", ['Date', 'Type', 'Clinician', 'Status/Diagnosis', 'Notes/Advice'], $rows);
        }

        $this->dispatch('notify', ['type' => 'error', 'message' => 'Export type not supported.']);
    }

    public function render()
    {
        $id = $this->patientId;

        $billsAll = Bill::where('patient_id', $id);

        // COUNTS: Only calculate once per request or when strictly necessary
        $counts = Cache::remember("patient_counts_{$id}", 60, function() use ($id) {
            return [
                'visits' => Consultation::where('patient_id', $id)->where('status', '!=', 'Cancelled')->count(),
                'bills' => Bill::where('patient_id', $id)->count(),
                'admissions' => Admission::where('patient_id', $id)->count(),
                'discharges' => Admission::where('patient_id', $id)->whereNotNull('discharge_date')->count(),
                'prescriptions' => Prescription::where('patient_id', $id)->count(),
                'labs' => LabOrder::where('patient_id', $id)->count(),
                'vitals' => PatientVital::where('patient_id', $id)->count(),
                'vaccinations' => PatientVaccination::where('patient_id', $id)->count(),
                'consents' => PatientConsent::where('patient_id', $id)->count(),
                'appointments' => Consultation::where('patient_id', $id)->whereDate('consultation_date', '>=', now()->toDateString())->where('status', '!=', 'Cancelled')->count(),
            ];
        });
        $counts['treatments'] = $counts['visits'] + $counts['admissions'] + $counts['prescriptions'];

        $latestVisits = Consultation::with(['doctor.department'])->where('patient_id', $id)->orderByDesc('consultation_date')->limit(5)->get();
        $latestBills = Bill::where('patient_id', $id)->orderByDesc('created_at')->limit(5)->get();
        $latestAdmissions = Admission::with(['bed.ward', 'doctor'])->where('patient_id', $id)->orderByDesc('admission_date')->limit(5)->get();
        $latestPrescriptions = Prescription::with(['doctor'])->where('patient_id', $id)->orderByDesc('created_at')->limit(5)->get();

        $timeline = collect();
        foreach ($latestVisits as $v) {
            $timeline->push((object)[
                'date' => $v->consultation_date,
                'type' => 'Visit',
                'title' => "OP Visit - Token #{$v->token_number}",
                'meta' => $v->doctor?->full_name,
                'color' => 'blue',
                'id' => $v->id,
                'print_route' => route('counter.opd.print', ['id' => $v->id])
            ]);
        }
        foreach ($latestAdmissions as $a) {
            $timeline->push((object)[
                'date' => $a->admission_date,
                'type' => 'IPD',
                'title' => "IPD Admission - {$a->admission_number}",
                'meta' => "Ward: " . ($a->bed?->ward?->name ?? 'N/A'),
                'color' => 'red',
                'id' => $a->id
            ]);
        }
        foreach ($latestPrescriptions as $p) {
            $timeline->push((object)[
                'date' => $p->created_at,
                'type' => 'Rx',
                'title' => "Prescription - {$p->diagnosis}",
                'meta' => $p->doctor?->full_name,
                'color' => 'emerald',
                'id' => $p->id,
                'print_route' => route('counter.prescriptions.print', ['id' => $p->id])
            ]);
        }

        $overview = [
            'latestVisits' => $latestVisits,
            'latestBills' => $latestBills,
            'latestAdmissions' => $latestAdmissions,
            'latestPrescriptions' => $latestPrescriptions,
            'timeline' => $timeline->sortByDesc('date')->take(6)->toArray()
        ];

        $treatmentPreview = [
            'visits' => Consultation::with(['doctor'])->where('patient_id', $id)->orderByDesc('consultation_date')->limit(10)->get(),
            'admissions' => Admission::with(['bed.ward', 'doctor'])->where('patient_id', $id)->orderByDesc('admission_date')->limit(10)->get(),
            'prescriptions' => Prescription::with(['doctor'])->where('patient_id', $id)->orderByDesc('created_at')->limit(10)->get(),
        ];

        $thirtyDaysAgo = now()->subDays(30);
        $totalThirtyDays = (clone $billsAll)
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('total_amount');

        $lastBill = (clone $billsAll)->latest()->first();
        $todayBill = (clone $billsAll)->whereDate('created_at', date('Y-m-d'))->first();

        $alerts = [];
        if ($this->patient->allergies) {
            $alerts[] = ['type' => 'danger', 'label' => 'Allergy', 'msg' => $this->patient->allergies];
        }
        
        $visitCount = Consultation::where('patient_id', $id)->where('consultation_date', '>=', now()->subMonths(3))->count();
        if ($visitCount > 5) {
            $alerts[] = ['type' => 'warning', 'label' => 'Frequent', 'msg' => "{$visitCount} visits in 3 months"];
        }

        $latestTemp = PatientVital::where('patient_id', $id)->latest()->value('temperature');
        if ($latestTemp && $latestTemp > 100) {
            $alerts[] = ['type' => 'danger', 'label' => 'High Temp', 'msg' => "{$latestTemp}°F recorded recently"];
        }

        $vaccinations = PatientVaccination::with('vaccine')->where('patient_id', $id)->get();
        $allVaccines = Vaccine::orderBy('sequence_order')->get();

        $datasets = [
            'bills' => null,
            'visits' => null,
            'admissions' => null,
            'discharges' => null,
            'prescriptions' => null,
            'labs' => null,
            'vitals' => null,
            'appointments' => null,
            'payments' => null,
            'consents' => null,
        ];

        if ($this->tab === 'billing') {
            $billsQuery = Bill::with(['items'])->where('patient_id', $id);
            $billsQuery = $this->applyDateFilter($billsQuery, 'created_at');
            
            // Also include Paid consultations that don't have a Bill record yet
            $unbilledConsultations = Consultation::where('patient_id', $id)
                ->where('payment_status', 'Paid')
                ->whereDoesntHave('bill');
            $unbilledConsultations = $this->applyDateFilter($unbilledConsultations, 'consultation_date');

            if ($this->status) {
                $billsQuery->where('payment_status', $this->status);
            }

            if ($this->search) {
                $term = '%' . $this->search . '%';
                $billsQuery->where(function ($q) use ($term) {
                    $q->where('bill_number', 'like', $term)->orWhere('notes', 'like', $term);
                });
            }

            // Union them manually because they are different models
            $formalBills = $billsQuery->get()->map(fn($b) => (object)[
                'id' => $b->id,
                'is_formal' => true,
                'bill_number' => $b->bill_number,
                'created_at' => $b->created_at,
                'total_amount' => $b->total_amount,
                'payment_method' => $b->payment_method,
                'payment_status' => $b->payment_status,
                'type' => 'Invoice'
            ]);

            $opdBills = $unbilledConsultations->get()->map(fn($c) => (object)[
                'id' => $c->id,
                'is_formal' => false,
                'bill_number' => 'OPD-' . $c->token_number,
                'created_at' => $c->created_at,
                'total_amount' => $c->fee,
                'payment_method' => $c->payment_method,
                'payment_status' => $c->payment_status,
                'type' => 'Registration'
            ]);

            $combined = $formalBills->concat($opdBills)->sortByDesc('created_at');
            
            // Manual pagination for combined collection
            $currentPage = $this->getPage();
            $datasets['bills'] = new \Illuminate\Pagination\LengthAwarePaginator(

                $combined->forPage($currentPage, $this->perPage),
                $combined->count(),
                $this->perPage,
                $currentPage,
                ['path' => \Illuminate\Support\Facades\Request::url(), 'query' => \Illuminate\Support\Facades\Request::query()]
            );
        }


        if ($this->tab === 'visits') {
            $query = Consultation::with(['doctor.department'])->where('patient_id', $id)->orderByDesc('consultation_date');
            $query = $this->applyDateFilter($query, 'consultation_date');
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('token_number', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('doctor', function ($dq) use ($term) {
                            $dq->where('full_name', 'like', $term);
                        });
                });
            }
            $datasets['visits'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'admissions') {
            $query = Admission::with(['bed.ward', 'doctor'])->where('patient_id', $id)->orderByDesc('admission_date');
            $query = $this->applyDateFilter($query, 'admission_date');
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('admission_number', 'like', $term)
                        ->orWhere('reason_for_admission', 'like', $term)
                        ->orWhereHas('doctor', function ($dq) use ($term) {
                            $dq->where('full_name', 'like', $term);
                        })
                        ->orWhereHas('bed.ward', function ($wq) use ($term) {
                            $wq->where('name', 'like', $term);
                        });
                });
            }
            $datasets['admissions'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'discharges') {
            $query = Admission::with(['bed.ward', 'doctor'])->where('patient_id', $id)->whereNotNull('discharge_date')->orderByDesc('discharge_date');
            $query = $this->applyDateFilter($query, 'discharge_date');
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('admission_number', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('doctor', function ($dq) use ($term) {
                            $dq->where('full_name', 'like', $term);
                        })
                        ->orWhereHas('bed.ward', function ($wq) use ($term) {
                            $wq->where('name', 'like', $term);
                        });
                });
            }
            $datasets['discharges'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'prescriptions') {
            $query = Prescription::with(['doctor'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('diagnosis', 'like', $term)
                        ->orWhere('chief_complaint', 'like', $term)
                        ->orWhereHas('doctor', function ($dq) use ($term) {
                            $dq->where('full_name', 'like', $term);
                        });
                });
            }
            $datasets['prescriptions'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'labs') {
            $query = LabOrder::with(['labTest', 'doctor'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('labTest', function ($tq) use ($term) {
                        $tq->where('name', 'like', $term);
                    })->orWhereHas('doctor', function ($dq) use ($term) {
                        $dq->where('full_name', 'like', $term);
                    });
                });
            }
            $datasets['labs'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'vitals') {
            $query = PatientVital::with(['consultation.doctor', 'recorder'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where('notes', 'like', $term);
            }
            $datasets['vitals'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'appointments') {
            $query = Consultation::with(['doctor.department'])->where('patient_id', $id)->whereDate('consultation_date', '>=', now()->toDateString())->orderBy('consultation_date');
            if ($this->status) {
                $query->where('status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('token_number', 'like', $term)->orWhereHas('doctor', function ($dq) use ($term) {
                        $dq->where('full_name', 'like', $term);
                    });
                });
            }
            $datasets['appointments'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'payments') {
            $query = Bill::where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            if ($this->status) {
                $query->where('payment_status', $this->status);
            }
            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where('bill_number', 'like', $term);
            }
            $datasets['payments'] = $query->paginate($this->perPage);
        }

        if ($this->tab === 'consents') {
            $query = PatientConsent::with(['creator'])
                ->where('patient_id', $id)
                ->orderByDesc('created_at');

            $query = $this->applyDateFilter($query, 'created_at');

            if ($this->search) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('original_name', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhere('type', 'like', $term);
                });
            }

            $datasets['consents'] = $query->paginate($this->perPage);
        }

        return view('livewire.counter.patient-history', [
            'billStats' => [
                'thirty_days' => $totalThirtyDays,
                'last_bill' => $lastBill,
                'today_bill' => $todayBill
            ],
            'alerts' => $alerts,
            'vaccinations' => $vaccinations,
            'allVaccines' => $allVaccines,
            'counts' => $counts,
            'overview' => $overview,
            'datasets' => $datasets,
            'treatmentPreview' => $treatmentPreview,
        ]);
    }

    public function recordVaccination($vaccineId, $date)
    {
        PatientVaccination::updateOrCreate(
            ['patient_id' => $this->patientId, 'vaccine_id' => $vaccineId],
            ['date_given' => $date ?: date('Y-m-d')]
        );
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Vaccination status updated!']);
    }
}
