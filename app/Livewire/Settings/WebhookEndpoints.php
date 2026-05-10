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
    public $endpoints;
    public $sources;
    public $activeTab = 'outbound'; // outbound, inbound
    
    public $showModal = false;
    public $editingEndpointId;
    public $editingSourceId;
    public $stats = [];
    public $selectedTestEvent = 'patient.registered';

    public function mount()
    {
        $this->loadStats();
        $this->loadData();
    }

    public function loadStats()
    {
        $last24Hours = now()->subDay();
        $baseQuery = WebhookLog::whereHas('endpoint', fn($q) => $q->where('created_by', auth()->id()))
            ->where('created_at', '>', $last24Hours);

        $logStats = $baseQuery->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count,
            AVG(duration_ms) as avg_latency
        ')->first();

        $this->stats = [
            'total' => WebhookEndpoint::where('created_by', auth()->id())->count(),
            'active' => WebhookEndpoint::where('created_by', auth()->id())->where('is_active', true)->count(),
            'success_rate' => $logStats->total_count > 0 
                ? round(($logStats->success_count / $logStats->total_count) * 100, 1) 
                : 100,
            'avg_latency' => round($logStats->avg_latency ?? 0),
            'pending_outbox' => \App\Models\WebhookOutbox::whereIn('status', ['pending', 'processing'])->count(),
            'trend' => $this->getHourlyTrend($last24Hours),
        ];
    }

    protected function getHourlyTrend($since)
    {
        return WebhookLog::whereHas('endpoint', fn($q) => $q->where('created_by', auth()->id()))
            ->where('created_at', '>', $since)
            ->selectRaw('HOUR(created_at) as hour, status, COUNT(*) as count')
            ->groupBy('hour', 'status')
            ->get()
            ->groupBy('hour');
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
        'Patient Management' => [
            'patient.registered' => 'Patient Registered',
            'patient.updated' => 'Patient Updated',
            'patient.deleted' => 'Patient Deleted',
        ],
        'OPD / Consultations' => [
            'appointment.booked' => 'Appointment Booked',
            'consultation.created' => 'Consultation Created',
            'consultation.completed' => 'Consultation Completed',
        ],
        'IPD / Admissions' => [
            'admission.created' => 'Admission Created',
            'admission.discharged' => 'Patient Discharged',
        ],
        'Billing & Payments' => [
            'invoice.paid' => 'Invoice Paid',
            'payment.received' => 'Payment Received',
        ],
        'Clinical Services' => [
            'prescription.created' => 'Prescription Created',
            'prescription.dispensed' => 'Prescription Dispensed',
            'medicine.low_stock' => 'Medicine Low Stock',
            'lab.order_created' => 'Lab Order Created',
            'lab.order_completed' => 'Lab Order Completed',
        ],
        'System Events' => [
            'daily.summary' => 'Daily Summary',
        ],
    ];

    #[Computed]
    public function flatAvailableEvents()
    {
        $flat = [];
        foreach ($this->availableEvents as $group) {
            $flat = array_merge($flat, $group);
        }
        return $flat;
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

    public function testEndpoint($id, \App\Services\WebhookService $service)
    {
        $endpoint = \App\Models\WebhookEndpoint::findOrFail($id);
        
        $data = $this->getMockDataForEvent($this->selectedTestEvent);
        
        $payload = $service->buildPayload($this->selectedTestEvent, $data);

        try {
            \App\Jobs\SendWebhookJob::dispatch($endpoint, $payload);
            $this->dispatch('notify', ['type' => 'success', 'message' => "Test '{$this->selectedTestEvent}' queued. Check logs."]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Failed to send test: ' . $e->getMessage()]);
        }
    }

    protected function getMockDataForEvent($event): array
    {
        return match($event) {
            'patient.registered', 'patient.updated' => [
                'id' => 1,
                'uhid' => 'P-2024-0001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'full_name' => 'John Doe',
                'phone' => '9876543210',
                'email' => 'john.doe@example.com',
                'gender' => 'Male',
                'age' => 30,
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
            'availableEvents' => $this->availableEvents
        ]);
    }
}
