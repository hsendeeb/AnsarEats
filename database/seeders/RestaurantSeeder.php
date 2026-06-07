<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Owner Users
        $owners = [
            [
                'name' => 'Mario El Khoury',
                'email' => 'mario@burgerplace.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Laila Haddad',
                'email' => 'laila@lebanesekitchen.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Giovanni Rossi',
                'email' => 'giovanni@pizzahouse.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($owners as $ownerData) {
            $user = User::create($ownerData);

            // 2. Create Restaurant for each Owner
            $restaurantData = $this->getRestaurantData($user->id, $ownerData['email']);
            $restaurant = Restaurant::create($restaurantData);

            // 3. Create Categories and Items
            $this->seedMenu($restaurant);
        }
    }

    private function getRestaurantData($userId, $email)
    {
        $data = [
            'mario@burgerplace.com' => [
                'user_id' => $userId,
                'name' => 'Classic Burger Joint',
                'description' => 'The best juicy smash burgers in the heart of Beirut. Fresh ingredients and our secret signature sauce.',
                'address' => 'Mar Mikhael, Beirut',
                'phone' => '+961 1 123 456',
                'is_open' => true,
            ],
            'laila@lebanesekitchen.com' => [
                'user_id' => $userId,
                'name' => 'Laila\'s Lebanese Kitchen',
                'description' => 'Authentic homemade Lebanese food. From hot mezze to traditional stews and grills.',
                'address' => 'Hamra Main Street, Beirut',
                'phone' => '+961 1 987 654',
                'is_open' => true,
            ],
            'giovanni@pizzahouse.com' => [
                'user_id' => $userId,
                'name' => 'Venezia Pizzeria',
                'description' => 'Real wood-fired Neapolitan pizza. Thin crust, fresh mozzarella, and imported Italian tomatoes.',
                'address' => 'Badaro, Beirut',
                'phone' => '+961 1 456 789',
                'is_open' => true,
            ],
        ];

        return $data[$email];
    }

    private function seedMenu($restaurant)
    {
        if ($restaurant->name === 'Classic Burger Joint') {
            $categories = [
                'Signatures' => [
                    ['name' => 'The OG Smash', 'description' => 'Double beef patty, cheddar, onions, pickles, and OG sauce.', 'price' => 12.50],
                    ['name' => 'Spicy Cowboy', 'description' => 'Beef patty, jalapeños, pepper jack, and spicy mayo.', 'price' => 13.99],
                ],
                'Sides' => [
                    ['name' => 'Truffle Fries', 'description' => 'Hand-cut fries with truffle oil and parmesan.', 'price' => 6.00],
                    ['name' => 'Onion Rings', 'description' => 'Beer-battered crispy onion rings.', 'price' => 5.50],
                ],
                'Drinks' => [
                    ['name' => 'Homemade Lemonade', 'description' => 'Freshly squeezed with mint.', 'price' => 4.00],
                    ['name' => 'Classic Shake', 'description' => 'Vanilla or Chocolate.', 'price' => 7.00],
                ],
            ];
        } elseif ($restaurant->name === 'Laila\'s Lebanese Kitchen') {
            $categories = [
                'Cold Mezze' => [
                    ['name' => 'Hummus', 'description' => 'Smooth chickpeas with tahini and olive oil.', 'price' => 5.00],
                    ['name' => 'Moutabal', 'description' => 'Smoked eggplant dip.', 'price' => 5.50],
                    ['name' => 'Tabbouleh', 'description' => 'Traditional parsley and burghul salad.', 'price' => 6.50],
                ],
                'Hot Mezze' => [
                    ['name' => 'Kibbeh', 'description' => 'Crispy fried meat and burghul balls.', 'price' => 8.00],
                    ['name' => 'Falafel Plate', 'description' => '6 pieces with tarator sauce.', 'price' => 7.00],
                ],
                'Grills' => [
                    ['name' => 'Shish Tawook', 'description' => 'Marinated chicken breast skewers with garlic paste.', 'price' => 15.00],
                    ['name' => 'Kafta Grill', 'description' => 'Grilled minced meat with parsley and onions.', 'price' => 16.50],
                ],
            ];
        } else {
            $categories = [
                'Pizzas' => [
                    ['name' => 'Margherita', 'description' => 'Tomato sauce, mozzarella, basil, and olive oil.', 'price' => 11.00],
                    ['name' => 'Diavola', 'description' => 'Tomato sauce, mozzarella, spicy salami, and chili.', 'price' => 14.50],
                    ['name' => 'Quattro Formaggi', 'description' => 'Mozzarella, gorgonzola, parmesan, and emmental.', 'price' => 15.00],
                ],
                'Pastas' => [
                    ['name' => 'Pesto Penne', 'description' => 'Fresh basil pesto and roasted pine nuts.', 'price' => 13.00],
                    ['name' => 'Lasagna', 'description' => 'Slow-cooked beef ragu and béchamel.', 'price' => 16.00],
                ],
            ];
        }

        $order = 0;
        foreach ($categories as $catName => $items) {
            $category = MenuCategory::create([
                'restaurant_id' => $restaurant->id,
                'name' => $catName,
                'sort_order' => $order++,
            ]);

            foreach ($items as $itemData) {
                MenuItem::create(array_merge($itemData, [
                    'menu_category_id' => $category->id,
                    'is_available' => true,
                ]));
            }
        }
    }
}
