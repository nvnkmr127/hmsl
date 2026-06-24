<?php

namespace App\Livewire\Reports;

use App\DTOs\ReportFilter;
use App\Services\ReportService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admission;
use App\Models\Doctor;
use App\Models\Ward;

class IpdReport extends Component
{
    use WithPagination;

    public $from;
    public $to;
    public $doctorId = '';
    public $wardId = '';
    
    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'wardId' => ['except' => ''],
    ];

    public function mount()
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public function updated($property)
    {
        if (in_array($property, ['from', 'to', 'doctorId', 'wardId'])) {
            $this->resetPage();
        }
    }

    public function render(ReportService $reportService)
    {
        $filter = new ReportFilter(
            from: $this->from,
            to: $this->to,
            doctorId: $this->doctorId ? (int) $this->doctorId : null,
            wardId: $this->wardId ? (int) $this->wardId : null
        );

        $stats = $reportService->getIpdStats($filter);

        $query = Admission::query()
            ->with(['patient', 'doctor', 'bed.ward'])
            ->whereBetween('admission_date', [$filter->from, $filter->to]);

        if ($filter->doctorId) {
            $query->where('doctor_id', $filter->doctorId);
        }

        if ($filter->wardId) {
            $query->whereHas('bed', fn($q) => $q->where('ward_id', $filter->wardId));
        }

        $admissions = $query->latest('admission_date')->paginate(10);

        return view('livewire.reports.ipd-report', [
            'stats' => $stats,
            'admissions' => $admissions,
            'doctors' => Doctor::where('is_active', true)->get(),
            'wards' => Ward::where('is_active', true)->get(),
        ]);
    }
}
