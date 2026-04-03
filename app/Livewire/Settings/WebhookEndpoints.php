<?php

namespace App\Livewire\Settings;

use App\Models\WebhookEndpoint;
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

    // Common fields
    #[Validate('required|string|max:150')]
    public $name;
    public $secret;
    public $authType = 'secret'; // secret, bearer, open

    // Outbound specific
    public $url;
    public $selectedEvents = [];

    // Inbound specific
    public $slug;

    protected $availableEvents = [
        'patient.registered' => 'Patient Registered',
        'admission.created' => 'IPD Admission',
        'invoice.paid' => 'Invoice Paid',
        'daily.summary' => 'System: Daily Summary',
        'consultation.completed' => 'OPD Consultation Completed',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->endpoints = \App\Models\WebhookEndpoint::latest()->get();
        $this->sources = \App\Models\WebhookSource::latest()->get();
    }

    public function openModal($id = null, $type = 'outbound')
    {
        $this->reset(['name', 'url', 'slug', 'selectedEvents', 'editingEndpointId', 'editingSourceId', 'authType']);
        $this->activeTab = $type;
        
        if ($id) {
            if ($type === 'outbound') {
                $endpoint = \App\Models\WebhookEndpoint::findOrFail($id);
                $this->editingEndpointId = $id;
                $this->name = $endpoint->name;
                $this->url = $endpoint->url;
                $this->secret = $endpoint->secret;
                $this->selectedEvents = $endpoint->events;
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
                'url' => 'required|url',
            ]);

            $data = [
                'name' => $this->name,
                'url' => $this->url,
                'secret' => $this->secret,
                'events' => $this->selectedEvents,
                'is_active' => true,
            ];

            if ($this->editingEndpointId) {
                \App\Models\WebhookEndpoint::find($this->editingEndpointId)->update($data);
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
        $this->showModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Configuration saved.']);
    }

    public function delete($id, $type = 'outbound')
    {
        if ($type === 'outbound') {
            \App\Models\WebhookEndpoint::findOrFail($id)->delete();
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
        $record->update(['is_active' => !$record->is_active]);
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.settings.webhook-endpoints', [
            'availableEvents' => $this->availableEvents
        ]);
    }
}
