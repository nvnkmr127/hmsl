<?php

namespace App\Livewire\Reports;

use App\DTOs\ReportFilter;
use App\Services\ReportService;
use Livewire\Component;
use Livewire\WithPagination;

class OpdReport extends Component
{
    use WithPagination;

    public $from;
    public $to;
    public $doctorId = '';
    public $departmentId = '';
    
    protected $queryString = [
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'doctorId' => ['except' => ''],
        'departmentId' => ['except' => ''],
    ];

    public function mount()
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public function updated($property)
    {
        if (in_array($property, ['from', 'to', 'doctorId', 'departmentId'])) {
            $this->resetPage();
        }
    }

    public function render(ReportService $reportService)
    {
        $filter = new ReportFilter(
            from: $this->from,
            to: $this->to,
            doctorId: $this->doctorId ? (int) $this->doctorId : null,
            departmentId: $this->departmentId ? (int) $this->departmentId : null
        );

        $stats = $reportService->getOpdStats($filter);

        return view('livewire.reports.opd-report', [
            'stats' => $stats,
            'doctors' => \App\Models\Doctor::where('is_active', true)->get(),
            'departments' => \App\Models\Department::where('is_active', true)->get(),
        ]);
    }
}
