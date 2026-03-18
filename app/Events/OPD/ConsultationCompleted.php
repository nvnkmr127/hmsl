<?php

namespace App\Events\OPD;

use App\Models\Consultation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsultationCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Consultation $consultation) {}
}
