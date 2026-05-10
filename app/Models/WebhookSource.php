<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookSource extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'secret',
        'auth_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
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
