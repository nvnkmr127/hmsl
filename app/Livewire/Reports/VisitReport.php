<?php

namespace App\Livewire\Reports;

use App\Models\Consultation;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class VisitReport extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $status = 'all';
    public $visitType = 'all';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function render()
    {
        $visits = Consultation::with(['patient', 'doctor', 'service'])
            ->whereBetween('consultation_date', [$this->dateFrom, $this->dateTo])
            ->when($this->status !== 'all', fn($q) => $q->where('status', $this->status))
            ->when($this->visitType !== 'all', fn($q) => $q->where('visit_type', $this->visitType))
            ->latest('consultation_date')
            ->paginate(15);

        return view('livewire.reports.visit-report', [
            'visits' => $visits
        ]);
    }
}
