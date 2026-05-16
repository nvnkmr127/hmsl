<?php

namespace App\Sync\Models;

use Illuminate\Database\Eloquent\Model;

class SyncConflict extends Model
{
    protected $table = 'sync_conflicts';

    protected $fillable = [
        'table_name',
        'record_uuid',
        'local_data',
        'server_data',
        'resolution',
        'resolved_at',
    ];

    protected $casts = [
        'local_data' => 'array',
        'server_data' => 'array',
        'resolved_at' => 'datetime',
    ];
}
