<?php
namespace App\Events\Webhooks;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class UserRegisteredEvent { use Dispatchable, SerializesModels; public function __construct(public array $data, public ?string $source = null) {} }
