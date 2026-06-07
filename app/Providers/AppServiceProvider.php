<?php

namespace App\Providers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Models\Rating;
use App\Models\Restaurant;
use App\Support\PerformanceCache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $refreshCatalog = static fn () => PerformanceCache::bump([
            'home',
            'browse',
            'search',
            'restaurants',
            'restaurant-show',
            'owner-orders',
            'profile-orders',
        ]);

        Restaurant::saved($refreshCatalog);
        Restaurant::deleted($refreshCatalog);

        MenuCategory::saved($refreshCatalog);
        MenuCategory::deleted($refreshCatalog);

        MenuItem::saved($refreshCatalog);
        MenuItem::deleted($refreshCatalog);

        Rating::saved(static fn () => PerformanceCache::bump(['home', 'restaurants', 'restaurant-show']));
        Rating::deleted(static fn () => PerformanceCache::bump(['home', 'restaurants', 'restaurant-show']));

        Promotion::saved(static fn () => PerformanceCache::bump(['restaurant-show']));
        Promotion::deleted(static fn () => PerformanceCache::bump(['restaurant-show']));

        Order::saved(static fn () => PerformanceCache::bump(['owner-orders', 'profile-orders']));
        Order::deleted(static fn () => PerformanceCache::bump(['owner-orders', 'profile-orders']));

        OrderItem::saved(static fn () => PerformanceCache::bump(['home', 'profile-orders']));
        OrderItem::deleted(static fn () => PerformanceCache::bump(['home', 'profile-orders']));
    }
}
