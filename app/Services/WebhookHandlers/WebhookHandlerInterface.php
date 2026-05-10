<?php

namespace App\Services\WebhookHandlers;

use App\Models\InboundWebhook;

interface WebhookHandlerInterface
{
    /**
     * Determine if this handler supports the given webhook.
     */
    public function supports(InboundWebhook $webhook): bool;

    /**
     * Handle the incoming webhook.
     * 
     * @param InboundWebhook $webhook
     * @return void
     * @throws \Exception
     */
    public function handle(InboundWebhook $webhook): void;
}
