<?php

namespace App\Events\Pharmacy;

use App\Models\Medicine;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MedicineLowStock
{
    use Dispatchable;
    use SerializesModels;

    public Medicine $medicine;

    public function __construct(Medicine $medicine)
    {
        $this->medicine = $medicine;
    }
}

