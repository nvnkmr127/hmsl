<?php

namespace App\Events\Pharmacy;

use App\Models\Prescription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrescriptionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prescription;

    /**
     * Create a new event instance.
     */
    public function __construct(Prescription $prescription)
    {
        $this->prescription = $prescription;
    }
}
