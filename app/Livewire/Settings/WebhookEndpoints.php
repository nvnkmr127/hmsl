<?php

namespace App\Livewire\Settings;

use App\Models\WebhookEndpoint;
use App\Models\WebhookLog;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
use Illuminate\Support\Str;

class WebhookEndpoints extends Component
{
    public $activeTab = 'outbound'; // outbound, inbound
    
    public $showModal = false;
    public $editingEndpointId;
    public $editingSourceId;
    public $stats = [];
    public $selectedTestEvent = 'patient.registered';

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $last24Hours = now()->subDay();
        $isAdmin = auth()->user()->can('manage settings');

        // Outbound Stats
        $outboundQuery = WebhookLog::when(!$isAdmin, function($q) {
                $q->whereHas('endpoint', fn($eq) => $eq->where('created_by', auth()->id()));
            })
            ->where('created_at', '>', $last24Hours);

        $logStats = $outboundQuery->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count,
            AVG(duration_ms) as avg_latency
        ')->first();

        // Inbound Stats
        $inboundStats = \App\Models\InboundWebhook::where('created_at', '>', $last24Hours)
            ->selectRaw('
                COUNT(*) as total_received,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count
            ')->first();

        $this->stats = [
            'total_sent' => $logStats->total_count,
            'total_received' => $inboundStats->total_received,
            'success_rate' => $logStats->total_count > 0 
                ? round(($logStats->success_count / $logStats->total_count) * 100, 1) 
                : 100,
            'avg_latency' => round($logStats->avg_latency ?? 0),
            'pending_outbox' => \App\Models\WebhookOutbox::whereIn('status', ['pending', 'processing'])->count(),
            'top_failing' => WebhookLog::where('status', 'failed')
                ->where('created_at', '>', $last24Hours)
                ->with('endpoint')
                ->select('webhook_endpoint_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('webhook_endpoint_id')
                ->orderByDesc('count')
                ->limit(3)
                ->get(),
            'recent_failed_inbound' => \App\Models\InboundWebhook::where('status', 'failed')
                ->latest()
                ->limit(3)
                ->get(),
            'trend' => $this->getHourlyTrend($last24Hours),
        ];
    }

    protected function getHourlyTrend($since)
    {
        $isAdmin = auth()->user()->can('manage settings');
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $hourExpr = $driver === 'sqlite' ? "strftime('%H', created_at)" : 'HOUR(created_at)';
        
        return WebhookLog::when(!$isAdmin, function($q) {
                $q->whereHas('endpoint', fn($eq) => $eq->where('created_by', auth()->id()));
            })
            ->where('created_at', '>', $since)
            ->selectRaw("{$hourExpr} as hour, status, COUNT(*) as count")
            ->groupBy('hour', 'status')
            ->get()
            ->groupBy('hour')
            ->map(fn($group) => [
                'total' => $group->sum('count'),
                'has_failures' => $group->where('status', 'failed')->count() > 0
            ])
            ->toArray();
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

    #[Computed]
    public function availableEvents()
    {
        $events = config('webhooks.events', []);
        $grouped = [];
        foreach ($events as $key => $event) {
            $group = $event['group'] ?? 'Other';
            $grouped[$group][$key] = $event['label'] ?? $key;
        }
        return $grouped;
    }

    #[Computed]
    public function flatAvailableEvents()
    {
        return collect(config('webhooks.events', []))
            ->mapWithKeys(fn($event, $key) => [$key => $event['label'] ?? $key])
            ->toArray();
    }

    public function toggleAllEvents()
    {
        $allKeys = [];
        foreach ($this->availableEvents as $group) {
            $allKeys = array_merge($allKeys, array_keys($group));
        }

        if (count($this->selectedEvents) === count($allKeys)) {
            $this->selectedEvents = [];
        } else {
            $this->selectedEvents = $allKeys;
        }
    }


    // Removed loadData in favor of render-time loading

    public function openModal($id = null, $type = 'outbound')
    {
        $this->reset(['name', 'url', 'slug', 'secret', 'selectedEvents', 'editingEndpointId', 'editingSourceId', 'authType', 'apiVersion']);
        $this->activeTab = $type;

        if ($id) {
            if ($type === 'outbound') {
                $this->editingEndpointId = $id;
                $endpoint = \App\Models\WebhookEndpoint::find($id);
                $this->name = $endpoint->name;
                $this->url = $endpoint->url;
                $this->secret = '********';
                $this->selectedEvents = $endpoint->events ?? [];
                $this->apiVersion = $endpoint->api_version ?? 'v1';
            } else {
                $this->editingSourceId = $id;
                $source = \App\Models\WebhookSource::find($id);
                $this->name = $source->name;
                $this->slug = $source->slug;
                $this->secret = '********';
                $this->authType = $source->auth_type;
            }
        } else {
            $this->secret = Str::random(32);
        }

        $this->showModal = true;
    }

    public function save()
    {
        $record = null;

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
                'events' => $this->selectedEvents,
                'api_version' => $this->apiVersion,
                'is_active' => true,
                'created_by' => auth()->id(),
            ];

            if ($this->secret !== '********') {
                $data['secret'] = $this->secret;
            }

            if ($this->editingEndpointId) {
                $record = \App\Models\WebhookEndpoint::findOrFail($this->editingEndpointId);
                $this->authorize('update', $record);
                $record->update($data);
            } else {
                $record = \App\Models\WebhookEndpoint::create($data);
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
                'auth_type' => $this->authType,
                'is_active' => true,
            ];

            if ($this->secret !== '********') {
                $data['secret'] = $this->secret;
            }

            if ($this->editingSourceId) {
                $record = \App\Models\WebhookSource::findOrFail($this->editingSourceId);
                $record->update($data);
            } else {
                $record = \App\Models\WebhookSource::create($data);
            }
        }

        $this->loadStats();

        \App\Models\AuditLog::log(
            ($this->editingEndpointId || $this->editingSourceId) ? 'webhook.updated' : 'webhook.created',
            $record,
            [],
            $record->toArray(),
            ['webhook', $this->activeTab]
        );

        $this->showModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Configuration saved.']);
    }

    public function delete($id, $type = 'outbound')
    {
        if ($type === 'outbound') {
            $record = \App\Models\WebhookEndpoint::findOrFail($id);
            $this->authorize('delete', $record);
        } else {
            $record = \App\Models\WebhookSource::findOrFail($id);
        }

        \App\Models\AuditLog::log('webhook.deleted', $record, $record->toArray(), [], ['webhook', $type]);
        $record->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Integration removed permanently.']);
    }

    public function toggleStatus($id, $type = 'outbound')
    {
        if ($type === 'outbound') {
            $record = \App\Models\WebhookEndpoint::findOrFail($id);
            $this->authorize('update', $record);
        } else {
            $record = \App\Models\WebhookSource::findOrFail($id);
        }

        $record->update(['is_active' => !$record->is_active]);
        
        \App\Models\AuditLog::log(
            $record->is_active ? 'webhook.enabled' : 'webhook.disabled',
            $record,
            [],
            ['is_active' => $record->is_active],
            ['webhook', $type]
        );
        
        $this->loadStats();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Status updated.']);
    }

    public function testEndpoint($id, \App\Services\WebhookService $service)
    {
        $endpoint = \App\Models\WebhookEndpoint::findOrFail($id);
        $this->authorize('view', $endpoint);
        
        $correlationId = (string) \Illuminate\Support\Str::uuid();
        $data = $this->getMockDataForEvent($this->selectedTestEvent);
        
        // Use standardized envelope
        $payload = \App\Services\Webhooks\Factories\WebhookPayloadFactory::createEnvelope(
            $this->selectedTestEvent, 
            $data, 
            $correlationId
        );
        
        try {
            \App\Jobs\SendWebhookJob::dispatchSync($endpoint, $payload, 1, $correlationId);
            $this->loadStats();
            $this->dispatch('notify', ['type' => 'success', 'message' => "Test '{$this->selectedTestEvent}' completed (ID: " . substr($correlationId, 0, 8) . "). Check logs."]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Test failed: ' . $e->getMessage()]);
        }
    }

    public function rotateSecret($id = null, $type = 'outbound')
    {
        if (!$id) {
            $this->secret = Str::random(32);
            return;
        }

        if ($type === 'outbound') {
            $record = \App\Models\WebhookEndpoint::findOrFail($id);
            $this->authorize('update', $record);
        } else {
            $record = \App\Models\WebhookSource::findOrFail($id);
        }

        $newSecret = Str::random(32);
        $record->update(['secret' => $newSecret]);
        $this->secret = $newSecret;

        \App\Models\AuditLog::log('webhook.secret_rotated', $record, [], [], ['webhook', $type]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Secret rotated successfully. Update your external systems immediately.']);
    }

    protected function getMockDataForEvent($event): array
    {
        return match($event) {
            'patient.registered', 'patient.updated' => [
                'uhid' => 'PAT-2026-0001',
                'name' => 'John Doe',
                'gender' => 'Male',
                'phone' => '9121658652',
                'email' => 'john.doe@example.com',
                'date_of_birth' => '1990-01-01',
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'Hyderabad',
                    'state' => 'Telangana',
                    'pincode' => '500001',
                ],
                'created_at' => now()->toISOString(),
            ],
            'appointment.booked' => [
                'consultation_id' => (string) \Illuminate\Support\Str::uuid(),
                'consultation_number' => 'CONS-2026-0001',
                'token_number' => '28',
                'patient_uhid' => 'PAT-2026-0001',
                'patient_name' => 'John Doe',
                'patient_phone' => '9121658652',
                'doctor_name' => 'Dr. Avinash Lakkampally',
                'visit_type' => 'New',
                'fees' => 500.00,
                'status' => 'Pending',
                'date' => now()->toDateString(),
                'created_at' => now()->toISOString(),
            ],
            'admission.created', 'admission.discharged' => [
                'id' => 1,
                'admission_number' => 'ADM-2024-001',
                'status' => $event === 'admission.created' ? 'Admitted' : 'Discharged',
                'patient' => ['id' => 1, 'uhid' => 'P-001', 'full_name' => 'John Doe'],
                'doctor' => ['id' => 5, 'full_name' => 'Dr. Smith'],
            ],
            'invoice.paid' => [
                'id' => 10,
                'bill_number' => 'BILL-2024-55',
                'total_amount' => 1500.00,
                'payment_method' => 'UPI',
            ],
            'medicine.low_stock' => [
                'id' => 45,
                'name' => 'Paracetamol 500mg',
                'stock_quantity' => 5,
                'min_stock_level' => 10,
            ],
            default => [
                'message' => 'Generic test payload',
                'timestamp' => now()->toIso8601String(),
            ],
        };
    }

    public function render()
    {
        return view('livewire.settings.webhook-endpoints', [
            'availableEvents' => $this->availableEvents,
            'endpoints' => \App\Models\WebhookEndpoint::latest()->get(),
            'sources' => \App\Models\WebhookSource::latest()->get(),
        ]);
    }
}
