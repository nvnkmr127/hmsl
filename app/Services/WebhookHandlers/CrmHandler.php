<?php

namespace App\Services\WebhookHandlers;

use App\Models\InboundWebhook;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

class CrmHandler implements WebhookHandlerInterface
{
    public function supports(InboundWebhook $webhook): bool
    {
        return $webhook->source === 'crm';
    }

    /**
     * Handle the incoming webhook from a CRM.
     */
    public function handle(InboundWebhook $webhook): void
    {
        $payload = $webhook->payload;
        $action = $payload['action'] ?? null;
        $data = $payload['data'] ?? [];

        if ($action === 'update_patient') {
            $this->updatePatient($data);
        }
    }

    protected function updatePatient(array $data): void
    {
        $uhid = $data['uhid'] ?? null;
        if (!$uhid) {
            throw new \App\Exceptions\WebhookValidationException("Missing UHID in CRM update payload.");
        }

        $patient = Patient::where('uhid', $uhid)->first();
        if (!$patient) {
            Log::warning("CRM requested update for non-existent patient: {$uhid}");
            return;
        }

        $allowedFields = ['phone', 'email', 'address', 'city', 'state', 'pincode'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (!empty($updateData)) {
            $patient->update($updateData);
        }
    }
}
