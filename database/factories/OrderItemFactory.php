<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'menu_item_id' => MenuItem::factory(),
            'name' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
