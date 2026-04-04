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
                    $query->where(function ($filteredQuery) use ($category) {
                        $filteredQuery
                            ->whereHas('menuCategory', function ($q) use ($category) {
                                $q->where('name', 'like', '%' . $category . '%');
                            })
                            ->orWhere('name', 'like', '%' . $category . '%');
                    });
                }

                return $query->paginate(20, ['*'], 'page', $page);
            }
        );
    }
}
