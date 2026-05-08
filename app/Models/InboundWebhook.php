<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundWebhook extends Model
{
    protected $fillable = [
        'source',
        'external_id',
        'payload',
        'headers',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    /**
     * Gracefully handle payload decryption for legacy logs.
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

    /**
     * Gracefully handle headers decryption.
     */
    public function getHeadersAttribute($value)
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

    public function setHeadersAttribute($value)
    {
        $this->attributes['headers'] = encrypt(is_array($value) ? json_encode($value) : $value);
    }
}
