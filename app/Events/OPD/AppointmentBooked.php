<?php

namespace App\Events\OPD;

use App\Models\Consultation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentBooked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $consultation;

    /**
     * Create a new event instance.
     */
    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }
}
