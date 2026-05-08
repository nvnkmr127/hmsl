<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_endpoint_id',
        'delivery_id',
        'event_name',
        'payload',
        'response_status',
        'response_body',
        'duration_ms',
        'attempt_number',
        'status',
        'error_message',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    /**
     * Gracefully handle payload decryption for legacy logs.
     */
    public function getPayloadAttribute($value)
    {
        if (is_array($value)) return $value;
        if (!$value) return [];

        try {
            // Try to decrypt if it looks like an encrypted string
            $decrypted = decrypt($value);
            return is_string($decrypted) ? json_decode($decrypted, true) : $decrypted;
        } catch (\Exception $e) {
            // Fallback to raw JSON if decryption fails
            return json_decode($value, true) ?? [];
        }
    }

    /**
     * Gracefully handle response_body decryption for legacy logs.
     */
    public function getResponseBodyAttribute($value)
    {
        if (!$value) return null;

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setPayloadAttribute($value)
    {
        $this->attributes['payload'] = encrypt(is_array($value) ? json_encode($value) : $value);
    }

    public function setResponseBodyAttribute($value)
    {
        $this->attributes['response_body'] = $value ? encrypt($value) : null;
    }

    public function endpoint()
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
