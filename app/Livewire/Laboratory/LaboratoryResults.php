<?php

namespace App\Livewire\Laboratory;

use App\Models\LabOrder;
use Livewire\Component;
use Livewire\WithPagination;

class LaboratoryResults extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $results = LabOrder::with(['patient', 'doctor', 'labTest', 'technician'])
            ->where('status', 'Completed')
            ->when($this->search, function ($q) {
                $q->whereHas('patient', fn($pq) => $pq->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('uhid', 'like', '%' . $this->search . '%'));
            })
            ->latest('completed_at')
            ->paginate(15);

        return view('livewire.laboratory.laboratory-results', compact('results'));
    }
}
