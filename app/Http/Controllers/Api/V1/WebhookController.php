<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InboundWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks.
     */
    public function handle(Request $request, string $source)
    {
        $config = \App\Models\WebhookSource::where('slug', $source)
            ->where('is_active', true)
            ->firstOrFail();

        // 1. Atomic Check & Create for idempotency
        $externalId = $request->header('X-Idempotency-Key') ?? $request->header('X-Request-ID') ?? $request->header('X-HMS-Delivery-ID');
        $correlationId = $request->header('X-HMS-Correlation-ID');
        
        $webhook = \App\Models\InboundWebhook::firstOrCreate(
            ['external_id' => $externalId],
            [
                'source' => $source,
                'payload' => $request->all(),
                'headers' => $request->headers->all(),
                'status' => 'pending',
                'correlation_id' => $correlationId,
            ]
        );

        if (!$webhook->wasRecentlyCreated && $externalId) {
            return response()->json([
                'message' => 'Webhook already received.',
                'id' => $webhook->id,
            ], 200);
        }

        try {
            // 2. Validate authentication based on config
            $this->authenticateRequest($request, $config);

            // 3. Dispatch job to process payload asynchronously
            \App\Jobs\ProcessWebhookJob::dispatch($webhook);

            return response()->json([
                'message' => 'Webhook received and queued for processing.',
                'id' => $webhook->id,
            ], 202);

        } catch (\Exception $e) {
            $webhook->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Authenticate the incoming request.
     */
    protected function authenticateRequest(Request $request, \App\Models\WebhookSource $config)
    {
        if ($config->auth_type === 'open') {
            return true;
        }

        if ($config->auth_type === 'bearer') {
            $token = $request->bearerToken();
            if ($token !== $config->secret) {
                throw new \Exception("Invalid bearer token.");
            }
            return true;
        }

        if ($config->auth_type === 'secret') {
            $signature = $request->header('X-HMS-Signature') ?? $request->header('X-Webhook-Signature');
            $timestamp = $request->header('X-HMS-Timestamp') ?? $request->header('X-Webhook-Timestamp');

            if (!$signature || !$timestamp) {
                throw new \Exception("Missing HMAC signature or timestamp header.");
            }

            // 1. Check timestamp window (15 minutes for inbound flexibility)
            if (abs(now()->timestamp - (int)$timestamp) > 900) {
                throw new \Exception("Webhook timestamp outside valid window.");
            }

            // 2. Verify signature
            if (!$this->verifyHmac($request->getContent(), $signature, $config->secret, (int)$timestamp)) {
                throw new \Exception("Invalid HMAC signature.");
            }
            return true;
        }
    }

    protected function verifyHmac(string $payload, string $signature, string $secret, int $timestamp): bool
    {
        $signedPayload = $timestamp . '.' . $payload;
        $computed = hash_hmac('sha256', $signedPayload, $secret);
        return hash_equals($computed, $signature);
    }
}
