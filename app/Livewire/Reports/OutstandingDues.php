<?php

namespace App\Livewire\Reports;

use App\Models\Bill;
use Livewire\Component;
use Livewire\WithPagination;

class OutstandingDues extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $dues = Bill::with(['patient'])
            ->where('balance_amount', '>', 0)
            ->where('payment_status', '!=', 'Cancelled')
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->whereHas('patient', fn($p) => $p->where('full_name', 'like', "%{$this->search}%")->orWhere('uhid', 'like', "%{$this->search}%"))
                      ->orWhere('bill_number', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(15);

        $totalOutstanding = Bill::where('payment_status', '!=', 'Cancelled')->sum('balance_amount');

        return view('livewire.reports.outstanding-dues', [
            'dues' => $dues,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }
}
