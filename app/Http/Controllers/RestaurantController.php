<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::query();

        if ($request->filled('location')) {
            $query->where('address', 'like', '%' . $request->location . '%');
        }

        // Load aggregates needed for sorting and display
        $query->withCount('menuCategories')
              ->withAvg('ratings', 'rating')
              ->withCount('ratings');

        // Apply sort based on rating or default
        $sort = $request->get('sort', 'recommended');
        switch ($sort) {
            case 'rating_desc':
                // Highest rated first, then by rating count, then latest
                $query->orderByDesc('ratings_avg_rating')
                      ->orderByDesc('ratings_count')
                      ->latest();
                break;
            case 'rating_asc':
                // Lowest rated first
                $query->orderBy('ratings_avg_rating')
                      ->orderByDesc('ratings_count')
                      ->latest();
                break;
            default:
                // Recommended: newest first
                $query->latest();
                break;
        }

        $restaurants = $query->get();
        
        // Get unique locations for the filter
        $locations = Restaurant::whereNotNull('address')
            ->distinct()
            ->pluck('address')
            ->map(function($address) {
                // Try to extract city or neighborhood if possible, or just use the address
                return head(explode(',', $address));
            })
            ->unique()
            ->filter();

        return view('restaurant.index', [
            'restaurants' => $restaurants,
            'locations' => $locations,
            'currentSort' => $sort,
        ]);
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['menuCategories' => function($query) {
            $query->with(['menuItems' => function($q) {
                // optionally filter available items or order them
            }]);
        }]);

        $restaurant->loadAvg('ratings', 'rating');
        $restaurant->loadCount('ratings');
        $restaurant->load(['ratings' => function($q) {
            $q->with('user')->latest()->take(10);
        }]);

        $userRating = null;
        if (auth()->check()) {
            $userRating = $restaurant->ratings()->where('user_id', auth()->id())->first();
        }

        return view('restaurant.show', compact('restaurant', 'userRating'));
    }
}
