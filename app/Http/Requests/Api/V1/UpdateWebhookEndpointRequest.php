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
            'events.*' => 'string|in:' . implode(',', array_keys(config('webhooks.events', []))),
            'is_active' => 'sometimes|boolean',
        ];
    }
}
