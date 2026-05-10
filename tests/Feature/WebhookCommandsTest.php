<?php

namespace Tests\Feature;

use App\Models\WebhookEndpoint;
use App\Models\WebhookLog;
use App\Models\InboundWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_outbound_command()
    {
        Queue::fake();
        $user = \App\Models\User::factory()->create(['id' => 1]);

        $endpoint = WebhookEndpoint::factory()->create(['is_active' => true, 'created_by' => $user->id]);
        $log = WebhookLog::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_name' => 'test.event',
            'payload' => ['foo' => 'bar'],
            'status' => 'failed',
            'created_at' => now()->subHours(2),
        ]);

        $this->artisan('webhooks:retry-outbound', ['--days' => 1])
             ->expectsConfirmation("Found 1 failed deliveries. Proceed with retry?", 'yes')
             ->assertExitCode(0);

        Queue::assertPushed(\App\Jobs\SendWebhookJob::class);
    }

    public function test_replay_inbound_command()
    {
        Queue::fake();

        $webhook = InboundWebhook::create([
            'source' => 'stripe',
            'payload' => ['id' => 'evt_123'],
            'status' => 'success',
            'created_at' => now()->subHours(2),
            'correlation_id' => 'old-cid',
        ]);

        $this->artisan('webhooks:replay-inbound', ['--days' => 1])
             ->expectsConfirmation("Found 1 webhooks. Proceed with replay?", 'yes')
             ->assertExitCode(0);

        $this->assertDatabaseCount('inbound_webhooks', 2);
        Queue::assertPushed(\App\Jobs\ProcessInboundWebhookJob::class);
    }

    public function test_prune_command()
    {
        $user = \App\Models\User::factory()->create(['id' => 1]);
        $log = WebhookLog::create([
            'webhook_endpoint_id' => WebhookEndpoint::factory()->create(['created_by' => $user->id])->id,
            'event_name' => 'old.event',
            'payload' => [],
            'status' => 'success',
        ]);
        
        $log->created_at = now()->subDays(40);
        $log->saveQuietly();

        $this->assertDatabaseCount('webhook_logs', 1);

        $this->artisan('webhooks:prune', ['--days' => 30, '--force' => true])
             ->assertExitCode(0);

        $this->assertDatabaseCount('webhook_logs', 0);
    }
}
