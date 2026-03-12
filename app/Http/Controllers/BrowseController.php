<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use Illuminate\Http\Request;

class BrowseController extends Controller
{
    // Predefined category keywords with emoji and labels
    public static function categories(): array
    {
        return [
            ['slug' => 'all',       'label' => 'All',       'emoji' => '🍽️'],
            ['slug' => 'sandwich',  'label' => 'Sandwiches','emoji' => '🥪'],
            ['slug' => 'burger',    'label' => 'Burgers',   'emoji' => '🍔'],
            ['slug' => 'pizza',     'label' => 'Pizza',     'emoji' => '🍕'],
            ['slug' => 'dessert',   'label' => 'Desserts',  'emoji' => '🍰'],
            ['slug' => 'drink',     'label' => 'Drinks',    'emoji' => '🥤'],
            ['slug' => 'salad',     'label' => 'Salads',    'emoji' => '🥗'],
            ['slug' => 'breakfast', 'label' => 'Breakfast', 'emoji' => '🍳'],
            ['slug' => 'pasta',     'label' => 'Pasta',     'emoji' => '🍝'],
            ['slug' => 'seafood',   'label' => 'Seafood',   'emoji' => '🦐'],
            ['slug' => 'chicken',   'label' => 'Chicken',   'emoji' => '🍗'],
        ];
    }

    public function index(Request $request)
    {
        $category = $request->query('category', 'all');
        $categories = self::categories();

        $items = $this->getItems($category);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'items' => $items->map(function ($meal) {
                    return [
                        'id'              => $meal->id,
                        'menu_item_id'    => $meal->id,
                        'name'            => $meal->name,
                        'description'     => $meal->description,
                        'price'           => number_format($meal->price, 2),
                        'raw_price'       => $meal->price,
                        'image'           => $meal->image ? asset('storage/' . $meal->image) : null,
                        'category_name'   => $meal->menuCategory->name ?? '',
                        'restaurant_id'   => $meal->menuCategory->restaurant->id ?? null,
                        'restaurant_user_id'=> $meal->menuCategory->restaurant->user_id ?? null,
                        'restaurant_name' => $meal->menuCategory->restaurant->name ?? '',
                        'restaurant_logo' => $meal->menuCategory->restaurant->logo
                            ? asset('storage/' . $meal->menuCategory->restaurant->logo)
                            : null,
                        'restaurant_initial' => strtoupper(substr($meal->menuCategory->restaurant->name ?? 'R', 0, 1)),
                        'url'             => route('restaurant.show', $meal->menuCategory->restaurant),
                        'is_featured'     => $meal->is_featured,
                        'variants'        => $meal->variants ?? [],
                    ];
                }),
                'current_user_id' => auth()->id()
            ]);
        }

        return view('browse', compact('categories', 'category', 'items'));
    }

    private function getItems(string $category)
    {
        $query = MenuItem::with(['menuCategory.restaurant'])
            ->where('is_available', true);

        if ($category !== 'all') {
            $query->whereHas('menuCategory', function ($q) use ($category) {
                $q->where('name', 'like', '%' . $category . '%');
            })->orWhere('name', 'like', '%' . $category . '%');
        }

        return $query->take(24)->get();
    }
}
