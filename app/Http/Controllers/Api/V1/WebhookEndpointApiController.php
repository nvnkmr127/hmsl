<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WebhookEndpointResource;
use App\Http\Resources\Api\V1\WebhookLogResource;
use App\Http\Requests\Api\V1\StoreWebhookEndpointRequest;
use App\Http\Requests\Api\V1\UpdateWebhookEndpointRequest;
use App\Models\WebhookEndpoint;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookEndpointApiController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(WebhookEndpoint::class, 'webhook_endpoint');
    }

    /**
     * Display a listing of webhook endpoints.
     */
    public function index(Request $request)
    {
        $endpoints = WebhookEndpoint::where('created_by', $request->user()->id)
            ->latest()
            ->paginate(20);

        return WebhookEndpointResource::collection($endpoints);
    }

    /**
     * Store a new webhook endpoint.
     */
    public function store(StoreWebhookEndpointRequest $request)
    {
        $endpoint = WebhookEndpoint::create([
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events,
            'secret' => 'whsec_' . Str::random(32),
            'is_active' => true,
            'created_by' => $request->user()->id,
        ]);

        return (new WebhookEndpointResource($endpoint))
            ->additional(['meta' => ['secret' => $endpoint->secret]]);
    }

    /**
     * Display the specified webhook endpoint.
     */
    public function show(WebhookEndpoint $webhook_endpoint)
    {
        return new WebhookEndpointResource($webhook_endpoint);
    }

    /**
     * Update the specified webhook endpoint.
     */
    public function update(UpdateWebhookEndpointRequest $request, WebhookEndpoint $webhook_endpoint)
    {
        $webhook_endpoint->update($request->validated());

        return new WebhookEndpointResource($webhook_endpoint);
    }

    /**
     * Remove the specified webhook endpoint.
     */
    public function destroy(WebhookEndpoint $webhook_endpoint)
    {
        $webhook_endpoint->delete();
        return response()->json(['message' => 'Webhook endpoint deleted successfully.']);
    }

    /**
     * Rotate the signing secret.
     */
    public function rotateSecret(WebhookEndpoint $webhook_endpoint)
    {
        $this->authorize('update', $webhook_endpoint);

        $webhook_endpoint->update([
            'secret' => 'whsec_' . Str::random(32)
        ]);

        return (new WebhookEndpointResource($webhook_endpoint))
            ->additional(['meta' => ['new_secret' => $webhook_endpoint->secret]]);
    }

    /**
     * Get delivery logs for a specific endpoint.
     */
    public function logs(Request $request, WebhookEndpoint $webhook_endpoint)
    {
        $this->authorize('view', $webhook_endpoint);

        $logs = $webhook_endpoint->logs()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return WebhookLogResource::collection($logs);
    }

    /**
     * Send a test webhook.
     */
    public function test(WebhookEndpoint $webhook_endpoint)
    {
        $this->authorize('update', $webhook_endpoint);

        $payload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toIso8601String(),
            'hospital' => config('app.name', 'HMS'),
            'data' => [
                'message' => 'This is a test webhook from the HMS API.',
                'triggered_by_api' => true,
            ]
        ];

        \App\Jobs\SendWebhookJob::dispatch($webhook_endpoint, $payload);

        return response()->json(['message' => 'Test webhook queued.']);
    }

    /**
     * List all available event types.
     */
    public function events()
    {
        return response()->json([
            'data' => [
                'patient.registered' => 'Patient Registered',
                'appointment.booked' => 'OPD Appointment Booked',
                'consultation.completed' => 'OPD Consultation Completed',
                'admission.created' => 'IPD Admission Created',
                'invoice.paid' => 'Invoice Paid',
                'payment.received' => 'Payment Received',
                'prescription.dispensed' => 'Prescription Dispensed',
                'medicine.low_stock' => 'Medicine Low Stock',
                'lab.order_created' => 'Lab Order Created',
                'lab.order_completed' => 'Lab Order Completed',
                'daily.summary' => 'System: Daily Summary',
            ]
        ]);
    }
}
