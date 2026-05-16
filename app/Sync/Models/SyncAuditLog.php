<?php

namespace App\Sync\Models;

use Illuminate\Database\Eloquent\Model;

class SyncAuditLog extends Model
{
    protected $table = 'sync_audit_log';

    protected $fillable = [
        'device_id',
        'action',
        'details',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'array',
    ];
}
