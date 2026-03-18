<?php

namespace App\Events\Billing;

use App\Models\Bill;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillSettled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bill;

    /**
     * Create a new event instance.
     */
    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }
}
