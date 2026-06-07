<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_category_id' => MenuCategory::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'is_available' => true,
            'variants' => null,
        ];
    }
}
