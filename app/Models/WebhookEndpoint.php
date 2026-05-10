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
        'api_version',
        'consecutive_failures',
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

    public function getSecretAttribute($value)
    {
        if (empty($value)) return $value;
        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $value;
        }
    }

    public function setSecretAttribute($value)
    {
        $this->attributes['secret'] = empty($value) ? $value : encrypt($value);
    }
}
