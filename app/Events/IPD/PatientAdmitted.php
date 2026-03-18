<?php

namespace App\Events\IPD;

use App\Models\Admission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientAdmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $admission;

    /**
     * Create a new event instance.
     */
    public function __construct(Admission $admission)
    {
        $this->admission = $admission;
    }
}
