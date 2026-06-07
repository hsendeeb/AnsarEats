<?php

namespace Database\Seeders;

use App\Models\CategoryTag;
use Illuminate\Database\Seeder;

class CategoryTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['slug' => 'sandwich', 'name' => 'Sandwiches', 'emoji' => '🥪'],
            ['slug' => 'burger', 'name' => 'Burgers', 'emoji' => '🍔'],
            ['slug' => 'pizza', 'name' => 'Pizza', 'emoji' => '🍕'],
            ['slug' => 'dessert', 'name' => 'Desserts', 'emoji' => '🍰'],
            ['slug' => 'drink', 'name' => 'Drinks', 'emoji' => '🥤'],
            ['slug' => 'salad', 'name' => 'Salads', 'emoji' => '🥗'],
            ['slug' => 'breakfast', 'name' => 'Breakfast', 'emoji' => '🍳'],
            ['slug' => 'pasta', 'name' => 'Pasta', 'emoji' => '🍝'],
            ['slug' => 'seafood', 'name' => 'Seafood', 'emoji' => '🦐'],
            ['slug' => 'chicken', 'name' => 'Chicken', 'emoji' => '🍗'],
        ];

        foreach ($tags as $index => $tag) {
            CategoryTag::updateOrCreate(
                ['slug' => $tag['slug']],
                [
                    'name' => $tag['name'],
                    'emoji' => $tag['emoji'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
