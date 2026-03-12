<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'variants' => 'array',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
