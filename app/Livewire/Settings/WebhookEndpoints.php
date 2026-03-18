<?php

namespace App\Livewire\Settings;

use App\Models\WebhookEndpoint;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;

class WebhookEndpoints extends Component
{
    public $endpoints;
    public $showModal = false;
    public $editingEndpointId;

    #[Validate('required|string|max:150')]
    public $name;

    #[Validate('required|url')]
    public $url;

    public $secret;

    public $selectedEvents = [];

    protected $availableEvents = [
        'patient.registered' => 'Patient Registered',
        'admission.created' => 'IPD Admission',
        'invoice.paid' => 'Invoice Paid',
        'daily.summary' => 'System: Daily Summary',
    ];

    public function mount()
    {
        $this->endpoints = WebhookEndpoint::latest()->get();
        $this->secret = Str::random(32);
    }

    public function openModal($id = null)
    {
        $this->reset(['name', 'url', 'selectedEvents', 'editingEndpointId']);
        
        if ($id) {
            $endpoint = WebhookEndpoint::findOrFail($id);
            $this->editingEndpointId = $id;
            $this->name = $endpoint->name;
            $this->url = $endpoint->url;
            $this->secret = $endpoint->secret;
            $this->selectedEvents = $endpoint->events;
        } else {
            $this->secret = Str::random(32);
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'url' => $this->url,
            'secret' => $this->secret,
            'events' => $this->selectedEvents,
            'is_active' => true,
        ];

        if ($this->editingEndpointId) {
            WebhookEndpoint::find($this->editingEndpointId)->update($data);
        } else {
            WebhookEndpoint::create($data);
        }

        $this->endpoints = WebhookEndpoint::latest()->get();
        $this->showModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Webhook endpoint saved.']);
    }

    public function delete($id)
    {
        WebhookEndpoint::findOrFail($id)->delete();
        $this->endpoints = WebhookEndpoint::latest()->get();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Endpoint removed.']);
    }

    public function toggleStatus($id)
    {
        $endpoint = WebhookEndpoint::findOrFail($id);
        $endpoint->update(['is_active' => !$endpoint->is_active]);
        $this->endpoints = WebhookEndpoint::latest()->get();
    }

    public function render()
    {
        return view('livewire.settings.webhook-endpoints', [
            'availableEvents' => $this->availableEvents
        ]);
    }
}
