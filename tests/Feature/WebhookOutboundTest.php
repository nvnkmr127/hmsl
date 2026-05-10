<?php

namespace Tests\Feature;

use App\Jobs\SendWebhookJob;
use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookOutboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_delivery_updates_endpoint_health(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'Test Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'supersecret',
            'events' => ['patient.registered'],
            'is_active' => true,
            'consecutive_failures' => 5
        ]);

        Http::fake([
            'example.com/*' => Http::response(['status' => 'ok'], 200)
        ]);

        $payload = ['event' => 'test.event', 'id' => 'evt-1', 'data' => []];
        $job = new SendWebhookJob($endpoint, $payload, 1, 'corr-123');
        $job->handle(app(WebhookService::class));

        $endpoint->refresh();
        $this->assertEquals(0, $endpoint->consecutive_failures);
        $this->assertNotNull($endpoint->last_success_at);
        
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-HMS-Signature') &&
                   $request->hasHeader('X-HMS-Delivery-ID');
        });
    }

    public function test_server_error_triggers_retry_and_increments_failures(): void
    {
        Queue::fake();

        $endpoint = WebhookEndpoint::create([
            'name' => 'Test Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'supersecret',
            'events' => ['patient.registered'],
            'is_active' => true,
        ]);

        Http::fake([
            'example.com/*' => Http::response('Server Error', 500)
        ]);

        $payload = ['event' => 'test.event', 'id' => 'evt-2', 'data' => []];
        $job = new SendWebhookJob($endpoint, $payload, 1, 'corr-123');
        $job->handle(app(WebhookService::class));

        $endpoint->refresh();
        $this->assertEquals(1, $endpoint->consecutive_failures);
        $this->assertNotNull($endpoint->last_failure_at);

        Queue::assertPushed(SendWebhookJob::class, function ($job) {
            return $job->attempt === 2;
        });
    }

    public function test_circuit_breaker_pauses_endpoint_after_threshold(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'Failing Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'secret',
            'events' => ['*'],
            'is_active' => true,
            'consecutive_failures' => 14 // One more will hit 15
        ]);

        Http::fake([
            'example.com/*' => Http::response('Error', 500)
        ]);

        $payload = ['event' => 'test.event', 'id' => 'evt-3', 'data' => []];
        (new SendWebhookJob($endpoint, $payload, 1, 'corr-123'))->handle(app(WebhookService::class));

        $endpoint->refresh();
        $this->assertFalse($endpoint->is_active);
    }

    public function test_auth_error_does_not_retry(): void
    {
        Queue::fake();

        $endpoint = WebhookEndpoint::create([
            'name' => 'Auth Failing Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'secret',
            'events' => ['*'],
            'is_active' => true,
        ]);

        Http::fake([
            'example.com/*' => Http::response('Unauthorized', 401)
        ]);

        $payload = ['event' => 'test.event', 'id' => 'evt-4', 'data' => []];
        (new SendWebhookJob($endpoint, $payload, 1, 'corr-123'))->handle(app(WebhookService::class));

        Queue::assertNotPushed(SendWebhookJob::class);
        
        $this->assertDatabaseHas('webhook_logs', [
            'error_category' => 'AUTH_ERROR'
        ]);
    }
    public function test_ssrf_blocked_url(): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'Internal Endpoint',
            'url' => 'http://169.254.169.254/latest/meta-data/',
            'secret' => 'secret',
            'events' => ['*'],
            'is_active' => true,
        ]);

        $payload = ['event' => 'test.event', 'id' => 'evt-5', 'data' => []];
        (new SendWebhookJob($endpoint, $payload, 1, 'corr-123'))->handle(app(WebhookService::class));

        $this->assertDatabaseHas('webhook_logs', [
            'webhook_endpoint_id' => $endpoint->id,
            'status' => 'failed',
            'error_category' => 'SSRF_BLOCKED'
        ]);
    }
}
