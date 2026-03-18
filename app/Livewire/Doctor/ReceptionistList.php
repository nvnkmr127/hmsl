<?php

namespace App\Livewire\Doctor;

use App\Models\User;
use Livewire\Component;

class ReceptionistList extends Component
{
    public function render()
    {
        $receptionists = User::role('receptionist')->get();
        return view('livewire.doctor.receptionist-list', compact('receptionists'));
    }
}
