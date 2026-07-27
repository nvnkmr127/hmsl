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

    #[On('patient-saved'), On('booking-completed')]
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
            $query = PatientVital::with(['recorder', 'consultation.doctor'])->where('patient_id', $id)->orderByDesc('created_at');
            $query = $this->applyDateFilter($query, 'created_at');
            
            $vitals = $query->get();
            $rows = $vitals->map(function (PatientVital $v) {
                return [
                    $v->created_at?->format('Y-m-d H:i'),
                    $v->consultation ? ('T#' . $v->consultation->token_number . ' - ' . ($v->consultation->doctor?->full_name ?? 'Any')) : 'N/A',
                    $v->weight ? $v->weight . ' kg' : '—',
                    $v->bp_systolic . ($v->bp_diastolic ? '/' . $v->bp_diastolic : ''),
                    $v->pulse ? $v->pulse . ' bpm' : '—',
                    $v->temperature ? $v->temperature . '°F' : '—',
                    $v->spo2 ? $v->spo2 . '%' : '—',
                    $v->recorder?->name
                ];
            });

            return $this->exportCsv("{$safeName}_vitals_{$date}.csv", ['Date/Time', 'OPD Visit', 'Weight', 'BP', 'Pulse', 'Temp', 'SpO2', 'Staff'], $rows);
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
        $counts['activity'] = $counts['visits'] + $counts['admissions'] + $counts['bills'] + $counts['prescriptions'] + $counts['labs'] + $counts['vitals'] + $counts['consents'] + 1;

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

        if ($this->tab === 'activity') {
            $id = $this->patientId;
            $uhid = $this->patient->uhid;

            $activities = collect();

            $fetchWithTrash = function($modelClass, $patientIdCol, $id) {
                $query = $modelClass::query()->where($patientIdCol, $id);
                if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($modelClass))) {
                    $query->withTrashed();
                }
                return $query->get();
            };

            // 1. Patient Profile Audit (Created, Edited, Soft-Deleted)
            if ($this->patient->created_at) {
                $activities->push((object)[
                    'timestamp' => $this->patient->created_at,
                    'category' => 'Patient Registry',
                    'action' => 'Patient Account Registered',
                    'details' => "Registered with UHID {$uhid}. Phone: " . ($this->patient->phone ?? 'N/A') . ", Address/City: " . ($this->patient->city ?? $this->patient->address ?? 'N/A'),
                    'user' => 'Registration Staff',
                    'color' => 'emerald',
                    'ref' => $uhid
                ]);
            }

            if ($this->patient->updated_at && $this->patient->created_at && $this->patient->updated_at->ne($this->patient->created_at)) {
                $activities->push((object)[
                    'timestamp' => $this->patient->updated_at,
                    'category' => 'Patient Profile',
                    'action' => 'Patient Profile Edited / Updated',
                    'details' => "Patient demographics or contact info updated. Name: {$this->patient->full_name}, Phone: " . ($this->patient->phone ?? 'N/A') . ", City: " . ($this->patient->city ?? 'N/A'),
                    'user' => 'Staff User',
                    'color' => 'amber',
                    'ref' => $uhid
                ]);
            }

            if (isset($this->patient->deleted_at) && $this->patient->deleted_at) {
                $activities->push((object)[
                    'timestamp' => $this->patient->deleted_at,
                    'category' => 'Patient Registry',
                    'action' => 'Patient Account Soft-Deleted',
                    'details' => "Patient account record was deleted/archived from active system registry.",
                    'user' => 'Admin User',
                    'color' => 'rose',
                    'ref' => $uhid
                ]);
            }

            // 2. OPD Consultations (Created, Updated/Edited, Deleted)
            $consultations = $fetchWithTrash(Consultation::class, 'patient_id', $id);
            foreach ($consultations as $c) {
                $activities->push((object)[
                    'timestamp' => $c->created_at ?: $c->consultation_date,
                    'category' => 'OPD Consultation',
                    'action' => "OP Token #{$c->token_number} Booked",
                    'details' => "Doctor: " . ($c->doctor?->full_name ?? 'OPD Doctor') . " | Visit Type: {$c->visit_type} | Service: " . ($c->service?->name ?? 'OPD') . " | Fee: ₹" . number_format($c->fee, 0) . " | Status: {$c->status} | Payment: {$c->payment_status}",
                    'user' => $c->doctor?->full_name ?? 'Counter Staff',
                    'color' => 'blue',
                    'ref' => "Token #{$c->token_number}"
                ]);

                if ($c->updated_at && $c->created_at && $c->updated_at->ne($c->created_at)) {
                    $activities->push((object)[
                        'timestamp' => $c->updated_at,
                        'category' => 'OPD Consultation',
                        'action' => "OP Token #{$c->token_number} Edited / Status Updated",
                        'details' => "Consultation updated. Current Status: {$c->status} | Payment: {$c->payment_status} | Fee: ₹" . number_format($c->fee, 0),
                        'user' => $c->doctor?->full_name ?? 'Duty Staff',
                        'color' => 'amber',
                        'ref' => "Token #{$c->token_number}"
                    ]);
                }

                if (isset($c->deleted_at) && $c->deleted_at) {
                    $activities->push((object)[
                        'timestamp' => $c->deleted_at,
                        'category' => 'OPD Consultation',
                        'action' => "OP Token #{$c->token_number} Cancelled / Deleted",
                        'details' => "Consultation token #{$c->token_number} was deleted or cancelled from patient record.",
                        'user' => 'Duty Staff',
                        'color' => 'rose',
                        'ref' => "Token #{$c->token_number}"
                    ]);
                }
            }

            // 3. IPD Admissions & Discharges (Created, Updated/Edited, Deleted)
            $admissions = $fetchWithTrash(Admission::class, 'patient_id', $id);
            foreach ($admissions as $a) {
                $activities->push((object)[
                    'timestamp' => $a->admission_date ?: $a->created_at,
                    'category' => 'IPD Admission',
                    'action' => "Admitted to IPD - {$a->admission_number}",
                    'details' => "Ward: " . ($a->bed?->ward?->name ?? 'N/A') . " (Bed #" . ($a->bed?->bed_number ?? 'N/A') . ") | Attending Doctor: " . ($a->doctor?->full_name ?? 'N/A') . " | Reason: " . ($a->reason_for_admission ?? 'General Admission') . " | Status: {$a->status}",
                    'user' => $a->doctor?->full_name ?? 'IPD Staff',
                    'color' => 'rose',
                    'ref' => $a->admission_number
                ]);

                if ($a->updated_at && $a->created_at && $a->updated_at->ne($a->created_at) && !$a->discharge_date) {
                    $activities->push((object)[
                        'timestamp' => $a->updated_at,
                        'category' => 'IPD Admission',
                        'action' => "IPD Admission Record #{$a->admission_number} Edited / Updated",
                        'details' => "Admission record modified. Current Status: {$a->status} | Bed: " . ($a->bed?->bed_number ?? 'N/A'),
                        'user' => $a->doctor?->full_name ?? 'IPD Staff',
                        'color' => 'amber',
                        'ref' => $a->admission_number
                    ]);
                }

                if ($a->discharge_date) {
                    $activities->push((object)[
                        'timestamp' => $a->discharge_date,
                        'category' => 'IPD Discharge',
                        'action' => "Discharged from IPD - {$a->admission_number}",
                        'details' => "Admitted for {$a->days_admitted} days | Discharged by: " . ($a->doctor?->full_name ?? 'Duty Doctor') . " | Summary Status: " . ($a->status),
                        'user' => $a->doctor?->full_name ?? 'Medical Officer',
                        'color' => 'purple',
                        'ref' => $a->admission_number
                    ]);
                }

                if (isset($a->deleted_at) && $a->deleted_at) {
                    $activities->push((object)[
                        'timestamp' => $a->deleted_at,
                        'category' => 'IPD Admission',
                        'action' => "IPD Admission Record #{$a->admission_number} Soft-Deleted",
                        'details' => "IPD Admission #{$a->admission_number} was soft-deleted from active system records.",
                        'user' => 'IPD Admin',
                        'color' => 'rose',
                        'ref' => $a->admission_number
                    ]);
                }
            }

            // 4. Bills & Invoices (Created, Updated/Edited, Deleted)
            $bills = $fetchWithTrash(Bill::class, 'patient_id', $id);
            foreach ($bills as $b) {
                $activities->push((object)[
                    'timestamp' => $b->created_at,
                    'category' => 'Billing & Invoice',
                    'action' => "Bill #{$b->bill_number} Generated",
                    'details' => "Total: ₹" . number_format($b->total_amount, 2) . " | Paid: ₹" . number_format($b->paid_amount, 2) . " | Balance: ₹" . number_format($b->balance_amount, 2) . " | Status: {$b->payment_status} | Method: " . ($b->payment_method ?? 'N/A'),
                    'user' => 'Billing Desk',
                    'color' => 'indigo',
                    'ref' => $b->bill_number
                ]);

                if ($b->updated_at && $b->created_at && $b->updated_at->ne($b->created_at)) {
                    $activities->push((object)[
                        'timestamp' => $b->updated_at,
                        'category' => 'Billing & Invoice',
                        'action' => "Bill #{$b->bill_number} Edited / Payment Status Updated",
                        'details' => "Updated Bill Details. Paid: ₹" . number_format($b->paid_amount, 2) . " | Balance: ₹" . number_format($b->balance_amount, 2) . " | Payment Status: {$b->payment_status}",
                        'user' => 'Billing Desk',
                        'color' => 'amber',
                        'ref' => $b->bill_number
                    ]);
                }

                if (isset($b->deleted_at) && $b->deleted_at) {
                    $activities->push((object)[
                        'timestamp' => $b->deleted_at,
                        'category' => 'Billing & Invoice',
                        'action' => "Bill #{$b->bill_number} Cancelled / Soft-Deleted",
                        'details' => "Bill #{$b->bill_number} (Amount: ₹" . number_format($b->total_amount, 2) . ") was soft-deleted/cancelled.",
                        'user' => 'Billing Admin',
                        'color' => 'rose',
                        'ref' => $b->bill_number
                    ]);
                }
            }

            // 5. Bill Payments & Money Receipts
            $payments = BillPayment::with(['bill', 'receiver'])->whereHas('bill', fn($bq) => $bq->where('patient_id', $id))->get();
            foreach ($payments as $p) {
                $activities->push((object)[
                    'timestamp' => $p->received_at ?: $p->created_at,
                    'category' => 'Payment Receipt',
                    'action' => "Payment Received - ₹" . number_format($p->amount, 2),
                    'details' => "Bill Ref: " . ($p->bill?->bill_number ?? 'N/A') . " | Type: " . ucfirst($p->type) . " | Method: " . ($p->method ?? 'Cash') . " | Receipt/Txn: " . ($p->reference ?? 'Direct Cash') . " | Received By: " . ($p->receiver?->name ?? 'Cashier'),
                    'user' => $p->receiver?->name ?? 'Cashier',
                    'color' => 'emerald',
                    'ref' => $p->bill?->bill_number ?? $uhid
                ]);
            }

            // 6. Prescriptions Issued (Created, Updated, Deleted)
            $prescriptions = $fetchWithTrash(Prescription::class, 'patient_id', $id);
            foreach ($prescriptions as $p) {
                $medCount = is_array($p->medicines) ? count($p->medicines) : 0;
                $activities->push((object)[
                    'timestamp' => $p->created_at,
                    'category' => 'Prescription',
                    'action' => "Prescription Issued by Dr. " . ($p->doctor?->full_name ?? 'Doctor'),
                    'details' => "Diagnosis: " . ($p->diagnosis ?? 'General Consultation') . " | Medicines Prescribed: {$medCount} items | Chief Complaint: " . ($p->chief_complaint ?? 'N/A'),
                    'user' => $p->doctor?->full_name ?? 'Prescribing Physician',
                    'color' => 'teal',
                    'ref' => "Rx #{$p->id}"
                ]);

                if ($p->updated_at && $p->created_at && $p->updated_at->ne($p->created_at)) {
                    $activities->push((object)[
                        'timestamp' => $p->updated_at,
                        'category' => 'Prescription',
                        'action' => "Prescription Rx #{$p->id} Edited / Modified",
                        'details' => "Prescription details updated. Diagnosis: " . ($p->diagnosis ?? 'N/A'),
                        'user' => $p->doctor?->full_name ?? 'Doctor',
                        'color' => 'amber',
                        'ref' => "Rx #{$p->id}"
                    ]);
                }

                if (isset($p->deleted_at) && $p->deleted_at) {
                    $activities->push((object)[
                        'timestamp' => $p->deleted_at,
                        'category' => 'Prescription',
                        'action' => "Prescription Rx #{$p->id} Soft-Deleted",
                        'details' => "Prescription Rx #{$p->id} was deleted from patient records.",
                        'user' => 'Doctor / Staff',
                        'color' => 'rose',
                        'ref' => "Rx #{$p->id}"
                    ]);
                }
            }

            // 7. Lab Diagnostic Orders (Created, Updated, Deleted)
            $labOrders = $fetchWithTrash(LabOrder::class, 'patient_id', $id);
            foreach ($labOrders as $lo) {
                $activities->push((object)[
                    'timestamp' => $lo->created_at,
                    'category' => 'Lab Diagnostics',
                    'action' => "Lab Order: " . ($lo->labTest?->name ?? 'Diagnostic Test'),
                    'details' => "Status: {$lo->status} | Prescribed by: " . ($lo->doctor?->full_name ?? 'Doctor') . " | Sample Collected: " . ($lo->collected_at ? \Illuminate\Support\Carbon::parse($lo->collected_at)->format('d M Y, h:i A') : 'Pending'),
                    'user' => $lo->doctor?->full_name ?? 'Lab Technician',
                    'color' => 'amber',
                    'ref' => "Lab #{$lo->id}"
                ]);

                if ($lo->updated_at && $lo->created_at && $lo->updated_at->ne($lo->created_at)) {
                    $activities->push((object)[
                        'timestamp' => $lo->updated_at,
                        'category' => 'Lab Diagnostics',
                        'action' => "Lab Order #{$lo->id} Result / Status Updated",
                        'details' => "Current Status: {$lo->status} | Test: " . ($lo->labTest?->name ?? 'Diagnostic Test'),
                        'user' => 'Lab Technician',
                        'color' => 'cyan',
                        'ref' => "Lab #{$lo->id}"
                    ]);
                }

                if (isset($lo->deleted_at) && $lo->deleted_at) {
                    $activities->push((object)[
                        'timestamp' => $lo->deleted_at,
                        'category' => 'Lab Diagnostics',
                        'action' => "Lab Order #{$lo->id} Cancelled / Soft-Deleted",
                        'details' => "Lab order #{$lo->id} was soft-deleted or cancelled.",
                        'user' => 'Lab Tech / Admin',
                        'color' => 'rose',
                        'ref' => "Lab #{$lo->id}"
                    ]);
                }
            }

            // 8. Patient Vitals Recordings
            $vitals = PatientVital::with(['recorder'])->where('patient_id', $id)->get();
            foreach ($vitals as $v) {
                $activities->push((object)[
                    'timestamp' => $v->created_at,
                    'category' => 'Vitals Recording',
                    'action' => "Vitals Recorded",
                    'details' => "BP: " . ($v->bp_systolic ? "{$v->bp_systolic}/{$v->bp_diastolic}" : 'N/A') . " | Pulse: " . ($v->pulse ? "{$v->pulse} bpm" : 'N/A') . " | Temp: " . ($v->temperature ? "{$v->temperature}°F" : 'N/A') . " | SpO2: " . ($v->spo2 ? "{$v->spo2}%" : 'N/A') . " | Weight: " . ($v->weight ? "{$v->weight} kg" : 'N/A'),
                    'user' => $v->recorder?->name ?? 'Triage Nurse',
                    'color' => 'cyan',
                    'ref' => "Vitals"
                ]);
            }

            // 9. Consents & Legal Uploads (Created, Soft-Deleted)
            $consents = $fetchWithTrash(PatientConsent::class, 'patient_id', $id);
            foreach ($consents as $cs) {
                $activities->push((object)[
                    'timestamp' => $cs->created_at,
                    'category' => 'Consent Form',
                    'action' => "Consent Form Signed: " . ucfirst(str_replace('_', ' ', $cs->type)),
                    'details' => "File: {$cs->original_name} | Signed At: " . ($cs->signed_at ? \Illuminate\Support\Carbon::parse($cs->signed_at)->format('d M Y') : 'N/A') . " | Notes: " . ($cs->notes ?? 'None'),
                    'user' => $cs->creator?->name ?? 'Records Officer',
                    'color' => 'rose',
                    'ref' => "Consent #{$cs->id}"
                ]);

                if (isset($cs->deleted_at) && $cs->deleted_at) {
                    $activities->push((object)[
                        'timestamp' => $cs->deleted_at,
                        'category' => 'Consent Form',
                        'action' => "Consent Form #{$cs->id} Deleted",
                        'details' => "Consent form '{$cs->original_name}' was soft-deleted from patient records.",
                        'user' => 'Records Officer',
                        'color' => 'rose',
                        'ref' => "Consent #{$cs->id}"
                    ]);
                }
            }

            // 10. System Audit Logs (Created, Updated, Deleted, Restored)
            $auditLogs = \App\Models\AuditLog::with('user')
                ->where(function($aq) use ($id, $uhid) {
                    $aq->where(function($sub) use ($id) {
                        $sub->where('auditable_type', 'App\Models\Patient')
                            ->where('auditable_id', $id);
                    })
                    ->orWhere('tags', 'like', "%{$uhid}%");
                })->get();

            foreach ($auditLogs as $al) {
                $changeDiff = [];
                if (is_array($al->old_values) && is_array($al->new_values)) {
                    foreach ($al->new_values as $k => $v) {
                        $oldV = $al->old_values[$k] ?? 'N/A';
                        if ($oldV != $v) {
                            $changeDiff[] = "{$k}: {$oldV} → {$v}";
                        }
                    }
                }
                $diffText = count($changeDiff) > 0 ? "Edited Fields: " . implode(' | ', array_slice($changeDiff, 0, 5)) : "System Action: " . ucfirst($al->event);

                $activities->push((object)[
                    'timestamp' => $al->created_at,
                    'category' => 'System Audit Log',
                    'action' => "Audit Log: " . ucfirst($al->event) . " (" . class_basename($al->auditable_type) . ")",
                    'details' => "{$diffText} | URL: {$al->url} | IP: {$al->ip_address}",
                    'user' => $al->user?->name ?? 'System Audit',
                    'color' => $al->event === 'deleted' ? 'rose' : ($al->event === 'updated' ? 'amber' : 'gray'),
                    'ref' => "Audit #{$al->id}"
                ]);
            }

            // Filter system-wide activities by date range or search keyword
            if ($this->dateFrom) {
                $activities = $activities->filter(fn($a) => \Illuminate\Support\Carbon::parse($a->timestamp)->gte(\Illuminate\Support\Carbon::parse($this->dateFrom)));
            }
            if ($this->dateTo) {
                $activities = $activities->filter(fn($a) => \Illuminate\Support\Carbon::parse($a->timestamp)->lte(\Illuminate\Support\Carbon::parse($this->dateTo)->endOfDay()));
            }
            if ($this->search) {
                $term = strtolower($this->search);
                $activities = $activities->filter(function($a) use ($term) {
                    return str_contains(strtolower($a->action), $term) ||
                           str_contains(strtolower($a->details), $term) ||
                           str_contains(strtolower($a->category), $term) ||
                           str_contains(strtolower($a->user), $term) ||
                           str_contains(strtolower($a->ref), $term);
                });
            }

            // Sort chronologically descending (newest first)
            $sortedActivities = $activities->sortByDesc(fn($a) => \Illuminate\Support\Carbon::parse($a->timestamp)->timestamp)->values();

            // Paginate manually
            $currentPage = $this->getPage();
            $datasets['activity'] = new \Illuminate\Pagination\LengthAwarePaginator(
                $sortedActivities->forPage($currentPage, $this->perPage),
                $sortedActivities->count(),
                $this->perPage,
                $currentPage,
                ['path' => \Illuminate\Support\Facades\Request::url(), 'query' => \Illuminate\Support\Facades\Request::query()]
            );
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
