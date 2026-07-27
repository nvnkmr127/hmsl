<?php

namespace App\Livewire\Reports;

use App\Models\Bill;
use Livewire\Component;
use Livewire\WithPagination;

class OutstandingDuesReport extends Component
{
    use WithPagination;

    public $search = '';
    public $fromDate = '';
    public $toDate = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
    ];

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFromDate() { $this->resetPage(); }
    public function updatedToDate() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'fromDate', 'toDate']);
        $this->resetPage();
    }

    public function render()
    {
        $duesQuery = Bill::with(['patient', 'consultation.doctor', 'admission.doctor'])
            ->whereIn('payment_status', ['Unpaid', 'Partially Paid'])
            ->when($this->search, function($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('patient', fn($p) => $p->search($this->search))
                        ->orWhere('bill_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->fromDate, fn($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn($q) => $q->whereDate('created_at', '<=', $this->toDate));

        $totalOutstanding = (clone $duesQuery)->sum('balance_amount');
        $dues = (clone $duesQuery)->latest()->paginate(15);

        return view('livewire.reports.outstanding-dues-report', [
            'dues' => $dues,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }
}
