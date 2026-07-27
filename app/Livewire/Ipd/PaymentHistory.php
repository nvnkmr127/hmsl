<?php

namespace App\Livewire\Ipd;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Admission;

class PaymentHistory extends Component
{
    public Admission $admission;

    public function mount(Admission $admission)
    {
        $this->admission = $admission;
    }

    #[On('refresh')]
    public function render()
    {
        $this->admission->refresh();
        $this->admission->load('finalBill.payments');

        return view('livewire.ipd.payment-history');
    }
}
