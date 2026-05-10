<?php

namespace Tests\Feature;

use App\Exceptions\WebhookValidationException;
use App\Jobs\ProcessInboundWebhookJob;
use App\Models\InboundWebhook;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WebhookPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_processing_via_crm_handler(): void
    {
        $patient = Patient::factory()->create(['uhid' => 'UHID-123', 'phone' => '111']);
        
        $webhook = InboundWebhook::create([
            'source' => 'crm',
            'external_id' => 'evt_1',
            'payload' => [
                'action' => 'update_patient',
                'data' => ['uhid' => 'UHID-123', 'phone' => '999']
            ],
            'status' => 'pending',
            'correlation_id' => 'corr-1'
        ]);

        (new ProcessInboundWebhookJob($webhook))->handle();

        $this->assertDatabaseHas('inbound_webhooks', [
            'id' => $webhook->id,
            'status' => 'completed',
        ]);

        $this->assertEquals('999', $patient->fresh()->phone);
    }

    public function test_fails_when_handler_is_missing(): void
    {
        $webhook = InboundWebhook::create([
            'source' => 'unknown_source',
            'payload' => [],
            'status' => 'pending',
        ]);

        (new ProcessInboundWebhookJob($webhook))->handle();

        $this->assertDatabaseHas('inbound_webhooks', [
            'id' => $webhook->id,
            'status' => 'failed',
            'error_category' => 'missing_handler'
        ]);
    }

    public function test_validation_exception_marks_failed_without_retry(): void
    {
        $webhook = InboundWebhook::create([
            'source' => 'crm',
            'payload' => ['action' => 'update_patient', 'data' => []], // Missing UHID
            'status' => 'pending',
        ]);

        (new ProcessInboundWebhookJob($webhook))->handle();

        $this->assertDatabaseHas('inbound_webhooks', [
            'id' => $webhook->id,
            'status' => 'failed',
            'error_category' => 'validation_error'
        ]);
        
        $this->assertStringContainsString('Missing UHID', $webhook->fresh()->error_message);
    }

    public function test_webhook_receiver_prevents_duplicates(): void
    {
        $receiver = app(\App\Services\Webhooks\WebhookReceiver::class);
        $request = \Illuminate\Http\Request::create('/api/v1/webhooks/crm', 'POST', ['id' => 'evt_unique_100']);
        
        $receiver->storeInboundWebhook('crm', $request);
        $firstId = $receiver->getInboundWebhook()->id;

        $receiver->storeInboundWebhook('crm', $request);
        $secondId = $receiver->getInboundWebhook()->id;

        $this->assertEquals($firstId, $secondId);
    }

    public function test_transient_failure_rethrows_for_retry(): void
    {
        $webhook = InboundWebhook::create([
            'source' => 'crm',
            'payload' => ['action' => 'update_patient', 'data' => ['uhid' => 'UHID-999']],
            'status' => 'pending',
        ]);

        $this->expectException(\Exception::class);

        $this->mock(\App\Services\WebhookHandlers\CrmHandler::class, function ($mock) use ($webhook) {
            $mock->shouldReceive('supports')->andReturn(true);
            $mock->shouldReceive('handle')->with(\Mockery::on(function($arg) use ($webhook) {
                return $arg->id === $webhook->id;
            }))->andThrow(new \Exception("Database timeout"));
        });

        (new ProcessInboundWebhookJob($webhook))->handle();
    }
}
