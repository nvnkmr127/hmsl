<?php

namespace App\Livewire\Ipd;

use Livewire\Component;
use App\Models\Admission;

class EditDischargeNote extends Component
{
    public Admission $admission;
    public $notes;
    public $isEditing = false;

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
        $this->notes = $admission->notes;
    }

    public function save()
    {
        $this->admission->update([
            'notes' => $this->notes
        ]);
        $this->isEditing = false;
    }

    public function render()
    {
        return view('livewire.ipd.edit-discharge-note');
    }
}
