<?php

namespace App\Events\Patients;

use App\Models\Patient;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $patient;

    /**
     * Create a new event instance.
     */
    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
    }
}
