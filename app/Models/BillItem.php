<?php

namespace App\Models;

use App\Sync\Traits\HasSyncMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory, HasSyncMetadata;

    protected $fillable = [
        'bill_id',
        'item_name',
        'item_type',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];


    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
