<?php

namespace App\Services\Webhooks;

use App\Models\InboundWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebhookReceiver
{
    protected ?InboundWebhook $inboundWebhook = null;

    /**
     * Store the raw inbound webhook.
     */
    public function storeInboundWebhook(string $source, Request $request): self
    {
        // 1. Idempotency Check (if external_id provided in headers or payload)
        $externalId = $request->header('X-Idempotency-Key') 
                   ?? $request->input('id') 
                   ?? $request->input('event_id');

        if ($externalId && $existing = InboundWebhook::where('source', $source)->where('external_id', $externalId)->first()) {
            $this->inboundWebhook = $existing;
            return $this;
        }

        // 2. Create the record
        $this->inboundWebhook = InboundWebhook::create([
            'source' => $source,
            'external_id' => $externalId,
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'status' => 'pending',
            'correlation_id' => (string) Str::uuid(),
        ]);

        return $this;
    }

    /**
     * Validate the payload based on provider rules.
     */
    public function validatePayload(array $rules): self
    {
        if (!$this->inboundWebhook || empty($rules)) {
            return $this;
        }

        $validator = Validator::make($this->inboundWebhook->payload, $rules);

        if ($validator->fails()) {
            $this->inboundWebhook->update([
                'status' => 'failed',
                'error_message' => 'Validation failed: ' . json_encode($validator->errors()->all()),
            ]);
        }

        return $this;
    }

    /**
     * Dispatch the processing job.
     */
    public function dispatchProcessingJob(): void
    {
        if (!$this->inboundWebhook || $this->inboundWebhook->status === 'failed') {
            return;
        }

        // Dispatch background job for processing
        \App\Jobs\ProcessInboundWebhookJob::dispatch($this->inboundWebhook);
    }

    public function getInboundWebhook(): ?InboundWebhook
    {
        return $this->inboundWebhook;
    }
}
