<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

use App\Models\MenuItem;

class HomeController extends Controller
{
    public function index()
    {
        $query = Restaurant::withCount('menuCategories')->withAvg('ratings', 'rating')->withCount('ratings')->where('is_open', true);

        if (auth()->check()) {
            $query->where('user_id', '!=', auth()->id());
        }

        $restaurants = $query->take(6)->get();

        $trendingMeals = MenuItem::with(['menuCategory.restaurant'])
            ->where('is_available', true)
            ->has('orderItems')
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->take(6)
            ->get();

        return view('home', compact('restaurants', 'trendingMeals'));
    }
}
