<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\PerformanceCache;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort');

        if (!in_array($sort, ['rating_desc', 'rating_asc'], true)) {
            $sort = null;
        }

        $restaurants = PerformanceCache::remember(
            'restaurants',
            json_encode([
                'q' => (string) $request->get('q', ''),
                'location' => (string) $request->get('location', ''),
                'sort' => $sort ?? '',
                'page' => $request->get('page', 1),
            ]),
            now()->addSeconds(config('performance.cache_ttl.restaurants')),
            function () use ($sort) {
                $query = Restaurant::query();

                if (request()->filled('q')) {
                    $term = request()->get('q');
                    $query->where(function ($q) use ($term) {
                        $q->where('name', 'like', '%' . $term . '%')
                            ->orWhereHas('menuCategories.menuItems', function ($mi) use ($term) {
                                $mi->where('name', 'like', '%' . $term . '%');
                            });
                    });
                }

                if (request()->filled('location')) {
                    $query->where('address', 'like', '%' . request()->location . '%');
                }

                $query->withCount('menuCategories')
                    ->withAvg('ratings', 'rating')
                    ->withCount('ratings');

                switch ($sort) {
                    case 'rating_desc':
                        $query->orderByDesc('ratings_avg_rating')
                            ->orderByDesc('ratings_count')
                            ->latest();
                        break;
                    case 'rating_asc':
                        $query->orderBy('ratings_avg_rating')
                            ->orderByDesc('ratings_count')
                            ->latest();
                        break;
                    default:
                        $query->latest();
                        break;
                }

                return $query->paginate(20);
            }
        );

        if ($request->ajax()) {
            return response()->json([
                'html' => view('restaurant.partials.list', ['restaurants' => $restaurants])->render(),
                'hasMore' => $restaurants->hasMorePages(),
                'nextPage' => $restaurants->currentPage() + 1
            ]);
        }

        $locations = PerformanceCache::remember(
            'restaurants',
            'locations',
            now()->addSeconds(config('performance.cache_ttl.restaurants')),
            fn () => Restaurant::whereNotNull('address')
                ->distinct()
                ->pluck('address')
                ->map(function ($address) {
                    return head(explode(',', $address));
                })
                ->unique()
                ->filter()
                ->values()
        );

        return view('restaurant.index', [
            'restaurants' => $restaurants,
            'locations' => $locations,
            'currentSort' => $sort,
        ]);
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant = PerformanceCache::remember(
            'restaurant-show',
            'restaurant:'.$restaurant->id,
            now()->addSeconds(config('performance.cache_ttl.restaurant_show')),
            function () use ($restaurant) {
                $freshRestaurant = Restaurant::query()->findOrFail($restaurant->id);

                $freshRestaurant->load(['menuCategories' => function ($query) {
                    $query->where('is_visible', true)->with(['menuItems' => function ($q) {
                        //
                    }]);
                }]);

                $freshRestaurant->loadAvg('ratings', 'rating');
                $freshRestaurant->loadCount('ratings');
                $freshRestaurant->load(['ratings' => function ($q) {
                    $q->with('user')->latest()->take(10);
                }]);

                return $freshRestaurant;
            }
        );

        $userRating = null;
        if (Auth::check()) {
            $userRating = $restaurant->ratings()->where('user_id', Auth::id())->first();
        }

        return view('restaurant.show', compact('restaurant', 'userRating'));
    }
}
