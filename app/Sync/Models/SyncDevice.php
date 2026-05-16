<?php

namespace App\Sync\Models;

use Illuminate\Database\Eloquent\Model;

use Laravel\Sanctum\HasApiTokens;

class SyncDevice extends Model
{
    use HasApiTokens;

    protected $table = 'sync_devices';

    protected $fillable = [
        'device_uuid',
        'name',
        'os_version',
        'last_sync_at',
        'status',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];
}
