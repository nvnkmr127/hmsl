<?php

namespace App\Models;

use App\Sync\Traits\HasSyncMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory, HasSyncMetadata;

    protected $fillable = [
        'name',
        'category_id',
        'sku',
        'unit',
        'stock_quantity',
        'min_stock_level',
        'unit_price',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
