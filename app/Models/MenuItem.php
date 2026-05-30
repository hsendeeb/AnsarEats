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
        'tags' => 'array',
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

        $groupedPrice = $this->variantPriceFromGroups($label, $applyDiscount);

        if ($groupedPrice !== null) {
            return $groupedPrice;
        }

        foreach ($this->variantOptions() as $option) {
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

    private function variantPriceFromGroups(string $label, bool $applyDiscount = true): ?float
    {
        $groups = data_get($this->variants, 'groups', []);

        if (empty($groups)) {
            return null;
        }

        $parts = collect(explode(' / ', $label))
            ->map(fn (string $part): string => trim($part))
            ->filter()
            ->values();

        if ($parts->isEmpty()) {
            return null;
        }

        $selectedByType = [];

        foreach ($parts as $part) {
            if (! str_contains($part, ':')) {
                return null;
            }

            [$type, $optionLabel] = array_map('trim', explode(':', $part, 2));

            if ($type === '' || $optionLabel === '') {
                return null;
            }

            $selectedByType[mb_strtolower($type)][] = mb_strtolower($optionLabel);
        }

        $total = (float) $this->price;

        foreach ($groups as $group) {
            $groupType = trim((string) data_get($group, 'type', 'Option'));
            $groupKey = mb_strtolower($groupType);
            $isRequired = data_get($group, 'required', true) !== false;
            $selectedLabels = $selectedByType[$groupKey] ?? [];
            $optionsByLabel = collect(data_get($group, 'options', []))
                ->mapWithKeys(function ($option): array {
                    $optionLabel = trim((string) data_get($option, 'label'));

                    return $optionLabel === ''
                        ? []
                        : [mb_strtolower($optionLabel) => (float) data_get($option, 'price', 0)];
                });

            if ($isRequired && count($selectedLabels) !== 1) {
                return null;
            }

            if (! $isRequired && count($selectedLabels) !== count(array_unique($selectedLabels))) {
                return null;
            }

            foreach ($selectedLabels as $selectedLabel) {
                if (! $optionsByLabel->has($selectedLabel)) {
                    return null;
                }

                $total += (float) $optionsByLabel->get($selectedLabel);
            }

            unset($selectedByType[$groupKey]);
        }

        if (! empty($selectedByType)) {
            return null;
        }

        $total = round($total, 2);

        return $applyDiscount
            ? $this->discountedPriceFor($total)
            : $total;
    }

    public function variantOptions(): array
    {
        $options = data_get($this->variants, 'options', []);

        if (! empty($options)) {
            return $options;
        }

        $groups = data_get($this->variants, 'groups', []);

        if (empty($groups)) {
            return [];
        }

        $combinations = [
            ['parts' => [], 'price' => (float) $this->price],
        ];
        foreach ($groups as $group) {
            $groupType = trim((string) data_get($group, 'type', 'Option'));
            $groupOptions = data_get($group, 'options', []);
            $isRequired = data_get($group, 'required', true) !== false;
            $normalizedOptions = collect($groupOptions)
                ->map(function ($option): ?array {
                    $optionLabel = trim((string) data_get($option, 'label'));

                    if ($optionLabel === '') {
                        return null;
                    }

                    return [
                        'label' => $optionLabel,
                        'price' => (float) data_get($option, 'price', 0),
                    ];
                })
                ->filter()
                ->values();

            if (! $isRequired) {
                $normalizedOptions->prepend([
                    'label' => null,
                    'price' => 0,
                ]);
            }

            $nextCombinations = [];

            foreach ($combinations as $combination) {
                foreach ($normalizedOptions as $option) {
                    $optionLabel = data_get($option, 'label');
                    $parts = $combination['parts'];

                    if ($optionLabel !== null) {
                        $parts[] = "{$groupType}: {$optionLabel}";
                    }

                    $nextCombinations[] = [
                        'parts' => $parts,
                        'price' => $combination['price'] + (float) data_get($option, 'price', 0),
                    ];
                }
            }

            $combinations = $nextCombinations;
        }

        if (empty($combinations)) {
            return [];
        }

        return collect($combinations)
            ->filter(fn (array $combination): bool => ! empty($combination['parts']))
            ->map(fn (array $combination): array => [
                'label' => implode(' / ', $combination['parts']),
                'price' => round((float) $combination['price'], 2),
            ])
            ->values()
            ->all();
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
