<?php

namespace App\Livewire\Master;

use App\Models\ClinicalTemplate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ClinicalTemplateList extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';

    protected $queryString = ['search', 'type'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    #[On('template-saved')]
    public function render()
    {
        $templates = ClinicalTemplate::query()
            ->when($this->search, fn($q) => $q->where('content', 'like', '%' . $this->search . '%'))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->orderBy('type')
            ->orderBy('content')
            ->paginate(10);

        return view('livewire.master.clinical-template-list', compact('templates'));
    }

    public function delete($id)
    {
        ClinicalTemplate::destroy($id);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Template removed.']);
    }
}
