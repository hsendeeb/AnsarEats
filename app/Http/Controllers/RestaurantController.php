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

        $restaurants = $query->withCount('menuCategories')->latest()->get();
        
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

        return view('restaurant.index', compact('restaurants', 'locations'));
    }

    public function show(Restaurant $restaurant)
    {
        // Load the menu categories and their items, only if items are available
        $restaurant->load(['menuCategories' => function($query) {
            $query->with(['menuItems' => function($q) {
                // optionally filter available items or order them
            }]);
        }]);

        return view('restaurant.show', compact('restaurant'));
    }
}
