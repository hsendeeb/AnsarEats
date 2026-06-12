<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NewRestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = [
            [
                'name' => 'Tokyo Ramen Bar',
                'description' => 'Authentic Japanese ramen with rich broth, fresh noodles, and premium toppings.',
                'address' => 'Monot Street, Beirut',
                'phone' => '+961 70 111 222',
            ],
            [
                'name' => 'El Mexicano',
                'description' => 'Bold Mexican flavors — tacos, burritos, and fresh guacamole made daily.',
                'address' => 'Gemmayze Main Road, Beirut',
                'phone' => '+961 71 333 444',
            ],
            [
                'name' => 'Green Bowl',
                'description' => 'Healthy bowls, smoothies, and plant-based meals for a fresh lifestyle.',
                'address' => 'Sodeco Square, Beirut',
                'phone' => '+961 76 555 666',
            ],
            [
                'name' => 'Sushi Master',
                'description' => 'Premium sushi, sashimi, and maki rolls crafted by expert chefs.',
                'address' => 'Verdun, Beirut',
                'phone' => '+961 70 777 888',
            ],
            [
                'name' => 'The Shawarma Spot',
                'description' => 'Crispy shawarma wraps, garlic sauce, and all the classic fixings.',
                'address' => 'Hamra, Beirut',
                'phone' => '+961 71 999 000',
            ],
            [
                'name' => 'Spice Route',
                'description' => 'Indian curries, biryanis, and tandoori dishes with authentic spices.',
                'address' => 'Badaro, Beirut',
                'phone' => '+961 76 123 456',
            ],
            [
                'name' => 'Crêpe Affair',
                'description' => 'Sweet and savory French crêpes with creative fillings.',
                'address' => 'Mar Mikhael, Beirut',
                'phone' => '+961 70 789 012',
            ],
            [
                'name' => 'The Greek Table',
                'description' => 'Traditional Greek souvlaki, moussaka, and fresh Mediterranean salads.',
                'address' => 'Achrafieh, Beirut',
                'phone' => '+961 71 345 678',
            ],
            [
                'name' => 'Wok & Roll',
                'description' => 'Stir-fried noodles, fried rice, and Chinese takeout classics.',
                'address' => 'Downtown Beirut',
                'phone' => '+961 76 901 234',
            ],
            [
                'name' => 'Bella Napoli',
                'description' => 'Southern Italian cuisine — fresh pasta, seafood, and Neapolitan desserts.',
                'address' => 'Ashrafieh, Beirut',
                'phone' => '+961 70 567 890',
            ],
        ];

        foreach ($restaurants as $data) {
            $user = User::factory()->create();

            $restaurant = Restaurant::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'description' => $data['description'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'delivery_fee' => 0,
                'is_open' => true,
                'created_at' => now()->subDays(rand(0, 6))->subHours(rand(0, 23)),
                'updated_at' => now(),
            ]);

            $this->seedMenu($restaurant);
        }

        $this->command->info('Seeded 10 new restaurants within the past week.');
    }

    private function seedMenu(Restaurant $restaurant): void
    {
        $category = MenuCategory::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Signature Dishes',
            'sort_order' => 0,
        ]);

        MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => 'Chef\'s Special',
            'description' => 'Our signature dish made with the freshest ingredients.',
            'price' => rand(8, 20) + 0.99,
            'is_available' => true,
        ]);

        MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => 'House Favorite',
            'description' => 'A customer favorite you have to try at least once.',
            'price' => rand(6, 18) + 0.50,
            'is_available' => true,
        ]);
    }
}
