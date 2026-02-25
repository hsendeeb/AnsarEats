<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
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
