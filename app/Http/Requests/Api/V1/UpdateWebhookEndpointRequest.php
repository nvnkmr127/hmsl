<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookEndpointRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:150',
            'url' => 'sometimes|required|url',
            'events' => 'sometimes|required|array|min:1',
            'events.*' => 'string|in:' . implode(',', [
                'patient.registered', 'appointment.booked', 'consultation.completed',
                'admission.created', 'invoice.paid', 'payment.received',
                'prescription.dispensed', 'medicine.low_stock', 'lab.order_created',
                'lab.order_completed', 'daily.summary'
            ]),
            'is_active' => 'sometimes|boolean',
        ];
    }
}
