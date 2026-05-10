<?php

namespace Tests\Feature;

use App\Models\WebhookSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookInboundTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbound_invalid_signature()
    {
        $source = WebhookSource::create([
            'name' => 'Test Source',
            'slug' => 'test-source',
            'secret' => 'real-secret',
            'auth_type' => 'secret',
            'is_active' => true,
        ]);

        $payload = ['event' => 'test.event'];
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . json_encode($payload), 'wrong-secret');

        $response = $this->withHeaders([
            'X-HMS-Signature' => $signature,
            'X-HMS-Timestamp' => $timestamp,
        ])->postJson('/api/v1/webhooks/test-source', $payload);

        $response->assertStatus(401);
    }

    public function test_inactive_source()
    {
        $source = WebhookSource::create([
            'name' => 'Test Source',
            'slug' => 'test-source',
            'secret' => 'real-secret',
            'auth_type' => 'secret',
            'is_active' => false,
        ]);

        $payload = ['event' => 'test.event'];
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . json_encode($payload), 'real-secret');

        $response = $this->withHeaders([
            'X-HMS-Signature' => $signature,
            'X-HMS-Timestamp' => $timestamp,
        ])->postJson('/api/v1/webhooks/test-source', $payload);

        $response->assertStatus(401);
    }

    public function test_expired_timestamp()
    {
        $source = WebhookSource::create([
            'name' => 'Test Source',
            'slug' => 'test-source',
            'secret' => 'real-secret',
            'auth_type' => 'secret',
            'is_active' => true,
        ]);

        $payload = ['event' => 'test.event'];
        // Timestamp from 10 minutes ago
        $timestamp = (string) now()->subMinutes(10)->timestamp;
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . json_encode($payload), 'real-secret');

        $response = $this->withHeaders([
            'X-HMS-Signature' => $signature,
            'X-HMS-Timestamp' => $timestamp,
        ])->postJson('/api/v1/webhooks/test-source', $payload);

        $response->assertStatus(401);
    }
}
