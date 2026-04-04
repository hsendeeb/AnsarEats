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
        'discount_percentage' => 'decimal:2',
    ];

    public function saleDiscountPercentage(): ?float
    {
        if (! $this->is_on_sale) {
            return null;
        }

        if ($this->discount_percentage !== null) {
            $percentage = (float) $this->discount_percentage;

            return $percentage > 0 ? min($percentage, 100.0) : null;
        }

        $basePrice = (float) $this->price;
        $salePrice = $this->sale_price !== null ? (float) $this->sale_price : null;

        if ($basePrice <= 0 || $salePrice === null || $salePrice >= $basePrice) {
            return null;
        }

        return round((($basePrice - $salePrice) / $basePrice) * 100, 2);
    }

    public function isSaleActive(): bool
    {
        return $this->saleDiscountPercentage() !== null;
    }

    public function discountedPriceFor(float $price): float
    {
        $price = round($price, 2);
        $percentage = $this->saleDiscountPercentage();

        if ($percentage === null) {
            return $price;
        }

        return round(max($price * (1 - ($percentage / 100)), 0), 2);
    }

    public function effectivePrice(): float
    {
        return $this->discountedPriceFor((float) $this->price);
    }

    public function variantPrice(?string $label, bool $applyDiscount = true): ?float
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

            if ($price === null) {
                return null;
            }

            $numericPrice = (float) $price;

            return $applyDiscount
                ? $this->discountedPriceFor($numericPrice)
                : round($numericPrice, 2);
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
