<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookOutbox extends Model
{
    protected $table = 'webhook_outbox';

    protected $fillable = [
        'correlation_id',
        'event_type',
        'payload',
        'status',
        'dispatched_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'dispatched_at' => 'datetime',
    ];

    /**
     * Gracefully handle payload encryption if needed.
     */
    public function getPayloadAttribute($value)
    {
        if (is_array($value)) return $value;
        if (!$value) return [];

        try {
            $decrypted = decrypt($value);
            return is_string($decrypted) ? json_decode($decrypted, true) : $decrypted;
        } catch (\Exception $e) {
            return json_decode($value, true) ?? [];
        }
    }

    public function setPayloadAttribute($value)
    {
        $this->attributes['payload'] = encrypt(is_array($value) ? json_encode($value) : $value);
    }
}
