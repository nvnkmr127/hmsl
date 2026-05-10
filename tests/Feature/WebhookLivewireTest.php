<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WebhookSource;
use App\Models\WebhookLog;
use App\Models\InboundWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WebhookLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Use a generic gate check or role if using Spatie permissions.
        // Create the permission if it doesn't exist
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage settings']);
        $this->user->givePermissionTo('manage settings');
    }

    public function test_endpoints_component_renders()
    {
        WebhookEndpoint::create([
            'name' => 'Test Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'supersecret',
            'events' => ['patient.registered'],
            'is_active' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Settings\WebhookEndpoints::class)
            ->assertStatus(200)
            ->assertSee('Test Endpoint');
    }

    public function test_outbound_logs_component_renders()
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'Test Endpoint',
            'url' => 'https://example.com/webhook',
            'secret' => 'supersecret',
            'events' => ['patient.registered'],
            'is_active' => true,
        ]);

        WebhookLog::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_name' => 'patient.registered',
            'payload' => ['foo' => 'bar'],
            'status' => 'success',
            'duration_ms' => 100,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Settings\WebhookLogs::class)
            ->assertStatus(200)
            ->assertSee('patient.registered');
    }

    public function test_inbound_logs_component_renders()
    {
        InboundWebhook::create([
            'source' => 'stripe',
            'payload' => ['foo' => 'bar'],
            'status' => 'success',
            'correlation_id' => 'corr-1',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Settings\InboundWebhookLogs::class)
            ->assertStatus(200)
            ->assertSee('stripe');
    }
}
