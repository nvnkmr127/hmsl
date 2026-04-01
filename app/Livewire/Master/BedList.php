<?php

namespace App\Livewire\Master;

use App\Models\Bed;
use App\Models\Ward;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class BedList extends Component
{
    use WithPagination;

    public $search = '';
    public $wardFilter = '';

    #[On('bed-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function toggleAvailability($id)
    {
        $bed = Bed::findOrFail($id);
        $bed->update(['is_available' => !$bed->is_available]);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Bed availability updated!'
        ]);
    }

    public function deleteBed($id)
    {
        Bed::findOrFail($id)->delete();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Bed removed!'
        ]);
    }

    public function render()
    {
        $beds = Bed::with('ward')
            ->when($this->search, function($query) {
                $query->where('bed_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->wardFilter, function($query) {
                $query->where('ward_id', $this->wardFilter);
            })
            ->latest()
            ->paginate(15);

        $wards = Ward::where('is_active', true)->get();

        return view('livewire.master.bed-list', [
            'beds' => $beds,
            'wards' => $wards
        ]);
    }
}
