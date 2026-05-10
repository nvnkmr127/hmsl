<?php
namespace App\Listeners\Webhooks;
use Illuminate\Support\Facades\Log;
class LogWebhookEventListener { public function handle($event) { Log::info("Webhook Event Received: " . get_class($event), ['data' => $event->data]); } }
