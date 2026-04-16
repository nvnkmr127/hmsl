<?php

namespace App\Livewire\Reports;

use App\Models\Bill;
use Livewire\Component;
use Livewire\WithPagination;

class OutstandingDuesReport extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $dues = Bill::with(['patient', 'consultation.doctor'])
            ->whereIn('payment_status', ['Unpaid', 'Partially Paid'])
            ->when($this->search, function($q) {
                $q->whereHas('patient', fn($p) => $p->search($this->search))
                  ->orWhere('bill_number', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(15);

        return view('livewire.reports.outstanding-dues-report', [
            'dues' => $dues
        ]);
    }
}
