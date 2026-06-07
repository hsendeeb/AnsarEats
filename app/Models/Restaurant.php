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
        'delivery_fee' => 'decimal:2',
        'subscription_ends_at' => 'datetime',
        'subscription_reminder_sent_at' => 'datetime',
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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function customerBlocks()
    {
        return $this->hasMany(RestaurantCustomerBlock::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating') ?: 0;
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    // ── Subscription helpers ──────────────────────────────────

    /**
     * Whether the restaurant has an active (non-expired) subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    /**
     * Whether the subscription has expired.
     */
    public function isSubscriptionExpired(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    /**
     * Whether the subscription expires within the given number of days.
     */
    public function subscriptionExpiresWithinDays(int $days): bool
    {
        if (! $this->subscription_ends_at) {
            return false;
        }

        return $this->subscription_ends_at->isFuture()
            && $this->subscription_ends_at->diffInDays(now()) <= $days;
    }

    /**
     * Extend subscription by the given number of months from now (or from current end date if still active).
     */
    public function extendSubscription(int $months = 1): void
    {
        $base = $this->hasActiveSubscription()
            ? $this->subscription_ends_at
            : now();

        $this->update([
            'subscription_ends_at' => $base->copy()->addMonths($months),
            'subscription_reminder_sent_at' => null,
        ]);
    }

    /**
     * Human-readable subscription status label.
     */
    public function subscriptionStatusLabel(): string
    {
        if (! $this->subscription_ends_at) {
            return 'No subscription';
        }

        if ($this->subscription_ends_at->isPast()) {
            return 'Expired';
        }

        if ($this->subscriptionExpiresWithinDays(2)) {
            return 'Expiring soon';
        }

        return 'Active';
    }

    // ── End subscription helpers ──────────────────────────────

    protected static function booted()
    {
        static::creating(function ($restaurant) {
            if (!$restaurant->slug) {
                $restaurant->slug = static::generateUniqueSlug($restaurant->name);
            }

            // Grant a free 1-month subscription to new restaurants
            if (! $restaurant->subscription_ends_at) {
                $restaurant->subscription_ends_at = now()->addMonth();
            }
        });

        static::updating(function ($restaurant) {
            if ($restaurant->isDirty('name')) {
                $restaurant->slug = static::generateUniqueSlug($restaurant->name);
            }
        });
    }

    protected static function generateUniqueSlug($name)
    {
        $baseSlug = \Illuminate\Support\Str::slug($name);
        $baseSlug = $baseSlug ?: 'restaurant';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
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
