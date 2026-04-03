<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundWebhook extends Model
{
    protected $fillable = [
        'source',
        'payload',
        'headers',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'processed_at' => 'datetime',
    ];
}
