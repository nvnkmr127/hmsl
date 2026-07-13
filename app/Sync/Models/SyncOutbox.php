<?php

namespace App\Sync\Models;

use Illuminate\Database\Eloquent\Model;

class SyncOutbox extends Model
{
    protected $table = 'sync_outbox';

    protected $fillable = [
        'device_id',
        'table_name',
        'record_uuid',
        'action',
        'payload',
        'sync_version',
        'status',
        'error_message',
        'attempts',
        'retry_count',
        'last_error',
        'failed_at',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
