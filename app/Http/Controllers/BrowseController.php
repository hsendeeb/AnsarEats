<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use App\Support\PerformanceCache;

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
        $page = max(1, (int) $request->query('page', 1));
        $categories = self::categories();

        $items = $this->getItems($category, $page);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'items' => collect($items->items())->map(function ($meal) {
                    $restaurant = $meal->menuCategory->restaurant;

                    return [
                        'id'              => $meal->id,
                        'menu_item_id'    => $meal->id,
                        'name'            => $meal->name,
                        'description'     => $meal->description,
                        'price'           => number_format($meal->price, 2),
                        'raw_price'       => $meal->price,
                        'is_on_sale'      => $meal->is_on_sale,
                        'sale_price'      => $meal->sale_price !== null ? number_format($meal->sale_price, 2) : null,
                        'raw_sale_price'  => $meal->sale_price,
                        'discount_percentage' => $meal->saleDiscountPercentage(),
                        'image'           => $meal->image ? asset('storage/' . $meal->image) : null,
                        'category_name'   => $meal->menuCategory->name ?? '',
                        'restaurant_id'   => $restaurant?->id,
                        'restaurant_user_id'=> $restaurant?->user_id,
                        'restaurant_name' => $restaurant?->name ?? '',
                        'restaurant_address'=> $restaurant?->address,
                        'restaurant_logo' => $restaurant?->logo
                            ? asset('storage/' . $restaurant->logo)
                            : null,
                        'restaurant_initial' => strtoupper(substr($restaurant?->name ?? 'R', 0, 1)),
                        'restaurant_is_open_now' => $restaurant ? (bool) $restaurant->isOpenNow() : null,
                        'url'             => $restaurant
                            ? route('restaurant.show', $restaurant)
                            : null,
                        'is_featured'     => $meal->is_featured,
                        'variants'        => $meal->variants ?? [],
                    ];
                }),
                'current_user_id' => auth()->id(),
                'next_page_url'   => $items->nextPageUrl(),
                'has_more'        => $items->hasMorePages(),
                'total'           => $items->total(),
            ]);
        }

        return view('browse', compact('categories', 'category', 'items'));
    }

    private function getItems(string $category, int $page)
    {
        return PerformanceCache::remember(
            'browse',
            json_encode(['category' => $category, 'page' => $page]),
            now()->addSeconds(config('performance.cache_ttl.browse')),
            function () use ($category, $page) {
                $query = MenuItem::with(['menuCategory.restaurant'])
                    ->where('is_available', true);

                if ($category !== 'all') {
                    $query->whereJsonContains('tags', $category);
                }

                return $query->paginate(20, ['*'], 'page', $page);
            }
        );
    }
}
