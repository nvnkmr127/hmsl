<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookEndpointRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', [
                'patient.registered', 'appointment.booked', 'consultation.completed',
                'admission.created', 'invoice.paid', 'payment.received',
                'prescription.dispensed', 'medicine.low_stock', 'lab.order_created',
                'lab.order_completed', 'daily.summary'
            ]),
        ];
    }
}
