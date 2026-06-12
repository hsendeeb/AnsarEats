<?php

namespace App\Http\Controllers;

use App\Http\Resources\HomeRestaurantResource;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                    ->withCount('orders')
                    ->withAvg('ratings', 'rating')
                    ->withCount('ratings')
                    ->where('is_open', true)
                    ->orderByDesc('orders_count')
                    ->latest();

                return $query->take(6)->get();
            }
        );

        $newestRestaurants = Restaurant::query()
            ->where('created_at', '>=', now()->subWeek())
            ->latest()
            ->take(10)
            ->get();

        $trendingMeals = PerformanceCache::remember(
            'home',
            'trending-meals',
            now()->addSeconds(config('performance.cache_ttl.home')),
            fn () => MenuItem::with(['menuCategory.restaurant'])
                ->where('is_available', true)
                ->has('orderItems')
                ->withCount('orderItems')
                ->orderByDesc('order_items_count')
                ->take(15)
                ->get()
        );

        $allStores = $this->allStoresQuery($authUserId)->paginate(15);
        $initialAllStores = HomeRestaurantResource::collection($allStores->getCollection())->resolve();
        $allStoresNextPage = $allStores->hasMorePages() ? $allStores->currentPage() + 1 : null;

        return view('home', compact(
            'restaurants',
            'newestRestaurants',
            'trendingMeals',
            'initialAllStores',
            'allStoresNextPage',
        ));
    }

    public function stores(Request $request): JsonResponse
    {
        $page = max($request->integer('page', 1), 1);
        $stores = $this->allStoresQuery(auth()->id())->paginate(15, ['*'], 'page', $page);

        return response()->json([
            'data' => HomeRestaurantResource::collection($stores->getCollection())->resolve(),
            'next_page' => $stores->hasMorePages() ? $stores->currentPage() + 1 : null,
        ]);
    }

    protected function allStoresQuery(?int $authUserId): Builder
    {
        $query = Restaurant::query()
            ->withCount('menuCategories')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->withCount('orders')
            ->orderByDesc('is_open')
            ->orderByRaw('COALESCE(ratings_avg_rating, 0) DESC')
            ->orderByDesc('orders_count');

        return $query;
    }
}
