<?php

namespace App\Listeners;

use App\Events\PatientCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WebhookEventListener
{
    protected $webhookService;

    /**
     * Create the event listener.
     */
    public function __construct(\App\Services\WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle the event.
     */
    public function handle(PatientCreated $event): void
    {
        $this->webhookService->dispatch('patient.created', [
            'id' => $event->patient->id,
            'uhid' => $event->patient->uhid,
            'full_name' => $event->patient->full_name,
            'phone' => $event->patient->phone,
            'created_at' => $event->patient->created_at,
        ]);
    }
}
