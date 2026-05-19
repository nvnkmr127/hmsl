<?php

namespace App\Livewire\Ipd;

use App\Models\Admission;
use App\Models\LabOrder;
use Livewire\Attributes\On;
use Livewire\Component;

class IpdLabOrders extends Component
{
    public Admission $admission;

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
    }

    #[On('echo:lab-orders,Laboratory\\LabOrderCreated')]
    #[On('lab-order-created')]
    #[On('refresh-lab-orders')]
    public function refreshOrders()
    {
        $this->admission->load('labOrders');
    }

    public function getLabOrdersProperty()
    {
        return LabOrder::with(['labTest', 'doctor'])
            ->where('admission_id', $this->admission->id)
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.ipd.ipd-lab-orders');
    }
}
