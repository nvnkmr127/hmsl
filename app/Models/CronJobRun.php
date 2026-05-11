<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronJobRun extends Model
{
    protected $fillable = [
        'job_key',
        'command',
        'status',
        'exit_code',
        'error_message',
        'output_path',
        'host',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'exit_code' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
