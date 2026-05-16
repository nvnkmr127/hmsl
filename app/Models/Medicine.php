<?php

namespace App\Models;

use App\Sync\Traits\HasSyncMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory, HasSyncMetadata;

    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'category',
        'strength',
        'manufacturer',
        'buying_price',
        'selling_price',
        'stock_quantity',
        'min_stock_level',
        'expire_date',
        'is_active',

    ];

    protected $casts = [
        'expire_date' => 'date',
        'buying_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];
}
