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
}
