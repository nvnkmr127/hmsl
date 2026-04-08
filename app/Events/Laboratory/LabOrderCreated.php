<?php

namespace App\Events\Laboratory;

use App\Models\LabOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LabOrderCreated
{
    use Dispatchable;
    use SerializesModels;

    public LabOrder $order;

    public function __construct(LabOrder $order)
    {
        $this->order = $order;
    }
}

