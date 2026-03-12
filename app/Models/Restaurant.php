<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'operating_hours' => 'array',
        'is_open' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class)->orderBy('sort_order');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating') ?: 0;
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function isOpenNow()
    {
        if (!$this->is_open) return false;
        if (!$this->operating_hours) return true; // Default to open if no hours set

        $day = strtolower(now()->format('l'));
        $hours = $this->operating_hours[$day] ?? null;

        if (!$hours || ($hours['closed'] ?? false)) return false;

        $now = now()->format('H:i');
        return $now >= $hours['open'] && $now <= $hours['close'];
    }
}
