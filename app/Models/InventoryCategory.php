<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted()
    {
        static::saving(function ($category) {
            if ($category->isDirty('name') || empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }
}
