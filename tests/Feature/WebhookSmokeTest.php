<?php

namespace Tests\Feature;

use App\Models\WebhookEndpoint;
use App\Models\WebhookSource;
use App\Models\WebhookOutbox;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test inbound webhook route.
     */
    public function test_inbound_webhook_route_accepts_data(): void
    {
        Queue::fake();

        // 1. Setup a source
        $source = WebhookSource::create([
            'name' => 'Test Source',
            'slug' => 'test-source',
            'secret' => 'test-secret',
            'auth_type' => 'secret',
            'is_active' => true,
        ]);

        $payload = ['event' => 'test.event', 'data' => ['foo' => 'bar']];
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . json_encode($payload), 'test-secret');

        $response = $this->withHeaders([
            'X-HMS-Signature' => $signature,
            'X-HMS-Timestamp' => $timestamp,
        ])->postJson('/api/v1/webhooks/test-source', $payload);

        $response->assertStatus(202);
        $this->assertDatabaseHas('inbound_webhooks', [
            'source' => 'test-source',
            'status' => 'pending',
        ]);
        
        Queue::assertPushed(\App\Jobs\ProcessInboundWebhookJob::class);
    }

    /**
     * Test outbound webhook dispatching.
     */
    public function test_outbound_webhook_dispatch_creates_outbox_and_queues_job(): void
    {
        Queue::fake();

        // 1. Setup an endpoint
        $endpoint = WebhookEndpoint::create([
            'name' => 'Test Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'whsec_test',
            'events' => ['patient.registered'],
            'is_active' => true,
        ]);

        // 2. Dispatch event
        $service = app(WebhookService::class);
        $service->dispatch('patient.registered', ['id' => 123, 'name' => 'John Doe']);

        // 3. Verify outbox
        $this->assertDatabaseHas('webhook_outbox', [
            'event_type' => 'patient.registered',
            'status' => 'dispatched',
        ]);

        // 4. Verify job queued
        Queue::assertPushed(\App\Jobs\SendWebhookJob::class);
    }

    /**
     * The event key accepted when registering an endpoint must match the
     * key actually used when the daily summary is dispatched, otherwise
     * subscribed endpoints silently never receive the webhook.
     */
    public function test_endpoint_subscribed_to_daily_summary_receives_dispatch(): void
    {
        Queue::fake();

        $endpoint = WebhookEndpoint::create([
            'name' => 'Daily Summary Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'whsec_test',
            'events' => ['system.daily.summary'],
            'is_active' => true,
        ]);

        app(WebhookService::class)->dispatchDailySummary('2026-07-01');

        $this->assertDatabaseHas('webhook_outbox', [
            'event_type' => 'system.daily.summary',
            'status' => 'dispatched',
        ]);

        Queue::assertPushed(\App\Jobs\SendWebhookJob::class, function ($job) use ($endpoint) {
            return $job->endpoint->is($endpoint);
        });
    }

    /**
     * The API's allowed event list must stay in sync with the real webhook
     * catalog (config/webhooks.php), or endpoints registered via the API
     * can never be validly subscribed to events like system.daily.summary.
     */
    public function test_api_allowed_events_match_catalog(): void
    {
        $catalogEvents = array_keys(config('webhooks.events', []));

        $storeRules = (new \App\Http\Requests\Api\V1\StoreWebhookEndpointRequest())->rules();
        $updateRules = (new \App\Http\Requests\Api\V1\UpdateWebhookEndpointRequest())->rules();

        $this->assertStringContainsString('in:' . implode(',', $catalogEvents), $storeRules['events.*']);
        $this->assertStringContainsString('in:' . implode(',', $catalogEvents), $updateRules['events.*']);
    }
}
