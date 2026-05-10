<?php
namespace App\Events\Webhooks;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class PaymentReceivedEvent { use Dispatchable, SerializesModels; public function __construct(public array $data, public ?string $source = null) {} }
