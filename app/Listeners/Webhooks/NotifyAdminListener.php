<?php
namespace App\Listeners\Webhooks;
use Illuminate\Support\Facades\Log;
class NotifyAdminListener { public function handle($event) { // For now just log, could be Mail or Slack
    if (isset($event->data['critical']) && $event->data['critical']) { Log::alert("CRITICAL Webhook Event: " . get_class($event)); } } }
