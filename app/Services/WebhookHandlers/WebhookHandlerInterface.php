<?php

namespace App\Services\WebhookHandlers;

use App\Models\InboundWebhook;

interface WebhookHandlerInterface
{
    /**
     * Handle the incoming webhook.
     * 
     * @param InboundWebhook $webhook
     * @return void
     */
    public function handle(InboundWebhook $webhook): void;
}
