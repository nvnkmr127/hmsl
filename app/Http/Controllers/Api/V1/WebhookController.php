<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Webhooks\SignatureVerifier;
use App\Services\Webhooks\WebhookReceiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected SignatureVerifier $verifier,
        protected WebhookReceiver $receiver
    ) {}

    /**
     * Main entry point for inbound webhooks.
     */
    public function handle(Request $request, string $source)
    {
        try {
            // 1. Signature Verification
            $verification = $this->verifier->verify($request, $source);

            if (!$verification->isValid) {
                Log::warning("Webhook signature verification failed for [{$source}]", [
                    'error' => $verification->errorMessage,
                    'ip' => $request->ip(),
                    'correlation_id' => $request->header('X-Correlation-ID'),
                ]);
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => $verification->errorMessage
                ], 401);
            }

            // 2. Storage & Validation
            $rules = $this->getValidationRules($source);
            
            $webhook = $this->receiver
                ->storeInboundWebhook($source, $request)
                ->validatePayload($rules);

            $inboundRecord = $webhook->getInboundWebhook();

            if ($inboundRecord?->status === 'failed') {
                return response()->json([
                    'error' => 'Unprocessable Entity',
                    'message' => $inboundRecord->error_message
                ], 422);
            }

            // 3. Mark as verified if signature passed
            $inboundRecord?->update(['is_verified' => true]);

            // 4. Dispatch for background processing
            $webhook->dispatchProcessingJob();

            return response()->json([
                'status' => 'accepted',
                'id' => $inboundRecord?->id,
                'correlation_id' => $inboundRecord?->correlation_id
            ], 202);

        } catch (\Exception $e) {
            Log::error("Critical error receiving webhook from [{$source}]", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Provider-specific validation rules.
     */
    protected function getValidationRules(string $source): array
    {
        return match ($source) {
            'stripe' => [
                'id' => 'required|string',
                'type' => 'required|string',
                'data' => 'required|array',
            ],
            'github' => [
                'action' => 'nullable|string',
                'repository' => 'required|array',
                'sender' => 'required|array',
            ],
            'shopify' => [
                'id' => 'required',
                'domain' => 'required|string',
            ],
            'crm' => [
                'action' => 'required|string',
                'data' => 'required|array',
            ],
            default => []
        };
    }
}
