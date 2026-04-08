<?php

namespace App\Events\Billing;

use App\Models\BillPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable;
    use SerializesModels;

    public BillPayment $payment;

    public function __construct(BillPayment $payment)
    {
        $this->payment = $payment;
    }
}

