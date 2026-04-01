<?php

namespace App\Livewire\Master;

use App\Models\Ward;
use App\Models\Bed;
use App\Services\WardManager;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class WardList extends Component
{
    use WithPagination;

    public $search = '';

    #[On('ward-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function deleteWard($id, WardManager $manager)
    {
        $ward = Ward::findOrFail($id);
        $manager->deleteWard($ward);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Ward deleted!'
        ]);
    }

    public function render()
    {
        $wards = Ward::withCount(['beds', 'beds as available_beds_count' => function($query) {
                $query->where('is_available', true);
            }])
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('type', 'like', '%' . $this->search . '%');
            })

            ->latest()
            ->paginate(10);

        return view('livewire.master.ward-list', [
            'wards' => $wards
        ]);
    }
}
