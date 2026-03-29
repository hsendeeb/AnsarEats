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
        'is_on_sale' => 'boolean',
        'sale_price' => 'decimal:2',
    ];

    public function effectivePrice(): float
    {
        if ($this->is_on_sale && $this->sale_price !== null && (float) $this->sale_price < (float) $this->price) {
            return (float) $this->sale_price;
        }

        return (float) $this->price;
    }

    public function variantPrice(?string $label): ?float
    {
        $label = trim((string) $label);

        if ($label === '') {
            return null;
        }

        $options = data_get($this->variants, 'options', []);

        foreach ($options as $option) {
            if (mb_strtolower((string) data_get($option, 'label')) !== mb_strtolower($label)) {
                continue;
            }

            $price = data_get($option, 'price');

            return $price !== null ? (float) $price : null;
        }

        return null;
    }

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
