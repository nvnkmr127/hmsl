<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Exception;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $endpoint;
    public $payload;
    public $attempt;

    /**
     * Create a new job instance.
     */
    public function __construct(WebhookEndpoint $endpoint, array $payload, int $attempt = 1)
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;
        $this->attempt = $attempt;
    }

    /**
     * Execute the job.
     */
    public function handle(WebhookService $service)
    {
        $body = json_encode($this->payload);
        $signature = $service->sign($body, $this->endpoint->secret);

        try {
            $response = Http::withHeaders([
                'X-HMS-Signature' => $signature,
                'X-HMS-Event' => $this->payload['event'],
                'X-HMS-Timestamp' => now()->timestamp,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout($this->endpoint->timeout_seconds ?? 10)
            ->post($this->endpoint->url, $this->payload);

            $service->logDelivery(
                $this->endpoint,
                $this->payload['event'],
                $this->payload,
                $response,
                null,
                $this->attempt
            );

            if (!$response->successful()) {
                $this->retryOrExecute($response->body());
            }

        } catch (Exception $e) {
            $service->logDelivery(
                $this->endpoint,
                $this->payload['event'],
                $this->payload,
                null,
                $e->getMessage(),
                $this->attempt
            );

            $this->retryOrExecute($e->getMessage());
        }
    }

    protected function retryOrExecute($error)
    {
        if ($this->attempt < 5) {
            $delay = pow(5, $this->attempt); // Exponential backoff: 5, 25, 125, 625 seconds
            
            SendWebhookJob::dispatch($this->endpoint, $this->payload, $this->attempt + 1)
                ->delay(now()->addSeconds($delay));
        }
    }
}
