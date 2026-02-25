<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $query = Restaurant::withCount('menuCategories')->where('is_open', true);

        if (auth()->check()) {
            $query->where('user_id', '!=', auth()->id());
        }

        $restaurants = $query->get();
        return view('home', compact('restaurants'));
    }
}
