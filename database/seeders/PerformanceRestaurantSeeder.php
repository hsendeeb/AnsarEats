<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PerformanceRestaurantSeeder extends Seeder
{
    private const RESTAURANT_COUNT = 50;
    private const ITEMS_PER_RESTAURANT = 10;

    private array $categoryPool = [
        'Burgers',
        'Pizza',
        'Sandwiches',
        'Salads',
        'Desserts',
        'Drinks',
        'Breakfast',
        'Pasta',
        'Chicken',
        'Seafood',
        'Wraps',
        'Bowls',
    ];

    private array $itemPrefixes = [
        'Signature',
        'House',
        'Classic',
        'Loaded',
        'Spicy',
        'Crispy',
        'Fresh',
        'Grilled',
        'Chef',
        'Street',
    ];

    private array $itemSuffixes = [
        'Delight',
        'Special',
        'Combo',
        'Box',
        'Plate',
        'Stack',
        'Bite',
        'Feast',
        'Mix',
        'Favorite',
    ];

    public function run(): void
    {
        for ($index = 1; $index <= self::RESTAURANT_COUNT; $index++) {
            DB::transaction(function () use ($index) {
                $user = User::updateOrCreate(
                    ['email' => sprintf('perf-owner-%02d@ansareats.test', $index)],
                    [
                        'name' => sprintf('Perf Owner %02d', $index),
                        'password' => Hash::make('password'),
                        'role' => 'owner',
                        'email_verified_at' => now(),
                    ]
                );

                $restaurant = Restaurant::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => sprintf('%s Kitchen %02d', fake()->city(), $index),
                        'description' => fake()->sentence(16),
                        'address' => sprintf('%d %s, %s', fake()->buildingNumber(), fake()->streetName(), fake()->city()),
                        'phone' => fake()->e164PhoneNumber(),
                        'is_open' => (bool) random_int(0, 1),
                        'latitude' => fake()->latitude(33.7, 34.1),
                        'longitude' => fake()->longitude(35.4, 35.7),
                        'operating_hours' => $this->operatingHours(),
                    ]
                );

                $restaurant->menuCategories()->delete();

                $this->seedMenuForRestaurant($restaurant, $index);
            });
        }
    }

    private function seedMenuForRestaurant(Restaurant $restaurant, int $index): void
    {
        $categoryNames = collect($this->categoryPool)
            ->shuffle()
            ->take(3)
            ->values();

        $categories = $categoryNames->map(function (string $name, int $sortOrder) use ($restaurant) {
            return MenuCategory::create([
                'restaurant_id' => $restaurant->id,
                'name' => $name,
                'sort_order' => $sortOrder,
                'is_visible' => true,
            ]);
        })->values();

        for ($itemIndex = 1; $itemIndex <= self::ITEMS_PER_RESTAURANT; $itemIndex++) {
            $category = $categories[($itemIndex - 1) % $categories->count()];

            MenuItem::create([
                'menu_category_id' => $category->id,
                'name' => $this->menuItemName($category->name, $index, $itemIndex),
                'description' => fake()->sentence(14),
                'price' => fake()->randomFloat(2, 4, 38),
                'is_available' => fake()->boolean(90),
                'is_featured' => $itemIndex <= 2,
                'variants' => $itemIndex % 4 === 0 ? $this->variants() : null,
            ]);
        }
    }

    private function menuItemName(string $categoryName, int $restaurantIndex, int $itemIndex): string
    {
        $prefix = $this->itemPrefixes[($restaurantIndex + $itemIndex) % count($this->itemPrefixes)];
        $suffix = $this->itemSuffixes[($restaurantIndex + ($itemIndex * 2)) % count($this->itemSuffixes)];

        return trim(sprintf('%s %s %s', $prefix, Str::singular($categoryName), $suffix));
    }

    private function variants(): array
    {
        return [
            'type' => 'Size',
            'options' => [
                ['label' => 'Regular', 'price' => fake()->randomFloat(2, 4, 18)],
                ['label' => 'Large', 'price' => fake()->randomFloat(2, 8, 26)],
            ],
        ];
    }

    private function operatingHours(): array
    {
        return [
            'monday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
            'tuesday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
            'wednesday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
            'thursday' => ['open' => '09:00', 'close' => '22:00', 'closed' => false],
            'friday' => ['open' => '09:00', 'close' => '23:00', 'closed' => false],
            'saturday' => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
            'sunday' => ['open' => '10:00', 'close' => '21:00', 'closed' => false],
        ];
    }
}
