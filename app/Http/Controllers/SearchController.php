<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use App\Support\PerformanceCache;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json([
                'restaurants' => [],
                'meals' => []
            ]);
        }

        $payload = PerformanceCache::remember(
            'search',
            mb_strtolower(trim($query)),
            now()->addSeconds(config('performance.cache_ttl.search')),
            function () use ($query) {
                $restaurants = Restaurant::where('name', 'like', "%{$query}%")
                    ->limit(5)
                    ->get(['id', 'name', 'logo']);

                $meals = MenuItem::where('name', 'like', "%{$query}%")
                    ->with('menuCategory.restaurant')
                    ->limit(5)
                    ->get(['id', 'name', 'image', 'price', 'menu_category_id']);

                return [
                    'restaurants' => $restaurants->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'name' => $r->name,
                            'logo' => $r->logo ? asset('storage/' . $r->logo) : null,
                            'type' => 'restaurant',
                            'url' => route('restaurant.show', $r),
                        ];
                    })->values()->all(),
                    'meals' => $meals->map(function ($m) {
                        return [
                            'id' => $m->id,
                            'name' => $m->name,
                            'price' => $m->price,
                            'image' => $m->image ? asset('storage/' . $m->image) : null,
                            'restaurant_name' => $m->menuCategory->restaurant->name,
                            'type' => 'meal',
                            'url' => route('restaurant.show', $m->menuCategory->restaurant) . '#item-' . $m->id,
                        ];
                    })->values()->all(),
                ];
            }
        );

        return response()->json($payload);
    }
}
