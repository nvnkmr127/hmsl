<?php

namespace App\Livewire\Settings;

use App\Models\WebhookEndpoint;
use App\Models\WebhookLog;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;

class WebhookEndpoints extends Component
{
    public $endpoints;
    public $sources;
    public $activeTab = 'outbound'; // outbound, inbound
    
    public $showModal = false;
    public $editingEndpointId;
    public $editingSourceId;
    public $stats = [];

    public function mount()
    {
        $this->loadStats();
        $this->loadData();
    }

    public function loadStats()
    {
        $this->stats = [
            'total' => WebhookEndpoint::where('created_by', auth()->id())->count(),
            'active' => WebhookEndpoint::where('created_by', auth()->id())->where('is_active', true)->count(),
            'success_rate' => WebhookLog::whereHas('endpoint', fn($q) => $q->where('created_by', auth()->id()))
                ->where('created_at', '>', now()->subDay())
                ->count() > 0 
                ? round((WebhookLog::whereHas('endpoint', fn($q) => $q->where('created_by', auth()->id()))
                    ->where('created_at', '>', now()->subDay())
                    ->where('status', 'success')->count() / 
                  WebhookLog::whereHas('endpoint', fn($q) => $q->where('created_by', auth()->id()))
                    ->where('created_at', '>', now()->subDay())->count()) * 100, 1)
                : 0,
            'avg_latency' => round(WebhookLog::whereHas('endpoint', fn($q) => $q->where('created_by', auth()->id()))
                ->where('created_at', '>', now()->subDay())
                ->avg('duration_ms') ?? 0),
            'pending_outbox' => \App\Models\WebhookOutbox::whereIn('status', ['pending', 'processing'])->count(),
        ];
    }

    public function toggleAllEvents()
    {
        if (count($this->selectedEvents) === count($this->availableEvents)) {
            $this->selectedEvents = [];
        } else {
            $this->selectedEvents = array_keys($this->availableEvents);
        }
    }

    // Common fields
    #[Validate('required|string|max:150')]
    public $name;
    public $secret;
    public $authType = 'secret'; // secret, bearer, open

    // Outbound specific
    public $url;
    public $selectedEvents = [];
    public $apiVersion = 'v1';

    // Inbound specific
    public $slug;

    protected $availableEvents = [
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
    ];


    public function loadData()
    {
        $this->endpoints = \App\Models\WebhookEndpoint::latest()->get();
        $this->sources = \App\Models\WebhookSource::latest()->get();
    }

    public function openModal($id = null, $type = 'outbound')
    {
        $this->reset(['name', 'url', 'slug', 'selectedEvents', 'editingEndpointId', 'editingSourceId', 'authType', 'apiVersion']);
        $this->activeTab = $type;
        
        if ($id) {
            if ($type === 'outbound') {
                $endpoint = \App\Models\WebhookEndpoint::findOrFail($id);
                $this->editingEndpointId = $id;
                $this->name = $endpoint->name;
                $this->url = $endpoint->url;
                $this->secret = $endpoint->secret;
                $this->selectedEvents = $endpoint->events;
                $this->apiVersion = $endpoint->api_version;
            } else {
                $source = \App\Models\WebhookSource::findOrFail($id);
                $this->editingSourceId = $id;
                $this->name = $source->name;
                $this->slug = $source->slug;
                $this->secret = $source->secret;
                $this->authType = $source->auth_type;
            }
        } else {
            $this->secret = Str::random(32);
            if ($type === 'inbound') {
                $this->authType = 'secret';
            }
        }

        $this->showModal = true;
    }

    public function save()
    {
        if ($this->activeTab === 'outbound') {
            $this->validate([
                'name' => 'required|string|max:150',
                'url' => [
                    'required',
                    'url',
                    function ($attribute, $value, $fail) {
                        if (!\App\Helpers\WebhookSecurity::isSafeUrl($value)) {
                            $fail('The webhook URL must resolve to a public IP address (SSRF Protection).');
                        }
                    }
                ],
            ]);

            $data = [
                'name' => $this->name,
                'url' => $this->url,
                'secret' => $this->secret,
                'events' => $this->selectedEvents,
                'api_version' => $this->apiVersion,
                'is_active' => true,
                'created_by' => auth()->id(),
            ];

            if ($this->editingEndpointId) {
                $endpoint = \App\Models\WebhookEndpoint::findOrFail($this->editingEndpointId);
                $this->authorize('update', $endpoint);
                $endpoint->update($data);
            } else {
                \App\Models\WebhookEndpoint::create($data);
            }
        } else {
            $this->validate([
                'name' => 'required|string|max:150',
                'slug' => 'required|alpha_dash|unique:webhook_sources,slug,' . $this->editingSourceId,
                'authType' => 'required|in:secret,bearer,open',
            ]);

            $data = [
                'name' => $this->name,
                'slug' => $this->slug,
                'secret' => $this->secret,
                'auth_type' => $this->authType,
                'is_active' => true,
            ];

            if ($this->editingSourceId) {
                \App\Models\WebhookSource::find($this->editingSourceId)->update($data);
            } else {
                \App\Models\WebhookSource::create($data);
            }
        }

        $this->loadData();
        $this->loadStats(); // Refresh dashboard
        $this->showModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Configuration saved.']);
    }

    public function delete($id, $type = 'outbound')
    {
        if ($type === 'outbound') {
            $endpoint = \App\Models\WebhookEndpoint::findOrFail($id);
            $this->authorize('delete', $endpoint);
            $endpoint->delete();
        } else {
            \App\Models\WebhookSource::findOrFail($id)->delete();
        }
        $this->loadData();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Configuration removed.']);
    }

    public function toggleStatus($id, $type = 'outbound')
    {
        if ($type === 'outbound') {
            $record = \App\Models\WebhookEndpoint::findOrFail($id);
        } else {
            $record = \App\Models\WebhookSource::findOrFail($id);
        }
        
        if ($type === 'outbound') {
            $this->authorize('update', $record);
        }

        $record->update(['is_active' => !$record->is_active]);
        $this->loadData();
        $this->loadStats(); // Refresh dashboard
    }

    public function testEndpoint($id)
    {
        $endpoint = \App\Models\WebhookEndpoint::findOrFail($id);
        
        $payload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toIso8601String(),
            'hospital' => config('app.name', 'HMS'),
            'data' => [
                'message' => 'This is a test webhook from HMS.',
                'triggered_by' => auth()->user()->name,
            ]
        ];

        try {
            \App\Jobs\SendWebhookJob::dispatch($endpoint, $payload);
            $this->dispatch('notify', type: 'success', message: 'Test webhook queued. Check logs in a few seconds.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to send test: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.settings.webhook-endpoints', [
            'availableEvents' => $this->availableEvents
        ]);
    }
}
