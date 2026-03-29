<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

use App\Models\MenuItem;
use App\Support\PerformanceCache;

class HomeController extends Controller
{
    public function index()
    {
        $authUserId = auth()->id();

        $restaurants = PerformanceCache::remember(
            'home',
            'restaurants:user:'.($authUserId ?? 'guest'),
            now()->addSeconds(config('performance.cache_ttl.home')),
            function () use ($authUserId) {
                $query = Restaurant::withCount('menuCategories')
                    ->withAvg('ratings', 'rating')
                    ->withCount('ratings')
                    ->where('is_open', true);

                if ($authUserId) {
                    $query->where('user_id', '!=', $authUserId);
                }

                return $query->take(6)->get();
            }
        );

        $trendingMeals = PerformanceCache::remember(
            'home',
            'trending-meals',
            now()->addSeconds(config('performance.cache_ttl.home')),
            fn () => MenuItem::with(['menuCategory.restaurant'])
                ->where('is_available', true)
                ->has('orderItems')
                ->withCount('orderItems')
                ->orderByDesc('order_items_count')
                ->take(6)
                ->get()
        );

        return view('home', compact('restaurants', 'trendingMeals'));
    }
}
