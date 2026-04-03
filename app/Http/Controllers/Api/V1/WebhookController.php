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

        // 1. Log the webhook request
        $webhook = InboundWebhook::create([
            'source' => $source,
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'status' => 'pending',
        ]);

        try {
            // 2. Validate authentication based on config
            $this->authenticateRequest($request, $config);

            // 3. Dispatch job to process payload asynchronously
            // ProcessWebhookJob::dispatch($webhook);

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
            $signature = $request->header('X-Webhook-Signature');
            if (!$signature) {
                throw new \Exception("Missing HMAC signature header.");
            }

            if (!$this->verifyHmac($request->getContent(), $signature, $config->secret)) {
                throw new \Exception("Invalid HMAC signature.");
            }
            return true;
        }
    }

    protected function verifyHmac(string $payload, string $signature, string $secret): bool
    {
        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }
}
