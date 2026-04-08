<?php

namespace App\Livewire\Master;

use App\Models\LabTest;
use App\Services\LabService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LabList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';

    #[On('lab-updated')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function toggleActive($id, LabService $service)
    {
        $test = LabTest::findOrFail($id);
        $service->toggleActive($test);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Test status updated!'
        ]);
    }

    public function deleteTest($id, LabService $service)
    {
        $test = LabTest::findOrFail($id);
        $service->deleteTest($test);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Lab test removed!'
        ]);
    }

    public function render()
    {
        $labTests = LabTest::withCount('parameters')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('category', 'like', '%' . $this->search . '%');
            })

            ->when($this->categoryFilter, function($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->latest()
            ->paginate(10);

        $categories = LabTest::select('category')->distinct()->pluck('category');

        return view('livewire.master.lab-list', [
            'labTests' => $labTests,
            'categories' => $categories
        ]);
    }
}
