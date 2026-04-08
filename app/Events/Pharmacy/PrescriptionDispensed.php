<?php

namespace App\Events\Pharmacy;

use App\Models\Prescription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrescriptionDispensed
{
    use Dispatchable;
    use SerializesModels;

    public Prescription $prescription;

    public function __construct(Prescription $prescription)
    {
        $this->prescription = $prescription;
    }
}

