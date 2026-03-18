<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'timeout_seconds',
        'created_by',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
