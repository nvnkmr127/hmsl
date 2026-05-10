<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Patient;
use App\Models\WebhookOutbox;
use App\Services\WebhookService;
use App\Services\Webhooks\Factories\PatientPayloadFactory;
use App\Services\Webhooks\Factories\WebhookPayloadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookEventMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_envelope_structure(): void
    {
        $event = 'patient.registered';
        $data = ['foo' => 'bar'];
        $correlationId = 'corr-123';

        $envelope = WebhookPayloadFactory::createEnvelope($event, $data, $correlationId);

        $this->assertArrayHasKey('id', $envelope);
        $this->assertEquals($event, $envelope['event']);
        $this->assertEquals($correlationId, $envelope['correlation_id']);
        $this->assertArrayHasKey('hospital', $envelope);
        $this->assertEquals($data, $envelope['data']);
    }

    public function test_patient_payload_mapping(): void
    {
        $patient = Patient::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'uhid' => 'UHID-789'
        ]);

        $payload = PatientPayloadFactory::forPatient($patient);

        $this->assertEquals('UHID-789', $payload['uhid']);
        $this->assertEquals('John Doe', $payload['name']);
        $this->assertArrayHasKey('address', $payload);
    }

    public function test_webhook_service_dispatch_respects_catalog(): void
    {
        $service = app(WebhookService::class);
        
        // Dispatch known event
        $service->dispatch('patient.registered', ['id' => 1]);
        $this->assertDatabaseHas('webhook_outbox', ['event_type' => 'patient.registered']);

        // Dispatch unknown event (should log warning but still record for now, or we could block it)
        // For now our service just logs a warning.
        $service->dispatch('unknown.event', ['id' => 2]);
        $this->assertDatabaseHas('webhook_outbox', ['event_type' => 'unknown.event']);
    }

    public function test_correlation_id_propagation_from_request(): void
    {
        $correlationId = 'req-corr-999';
        request()->headers->set('X-Correlation-ID', $correlationId);

        $service = app(WebhookService::class);
        $service->dispatch('patient.registered', ['id' => 1]);

        $outbox = WebhookOutbox::latest()->first();
        $this->assertEquals($correlationId, $outbox->correlation_id);
    }
}
