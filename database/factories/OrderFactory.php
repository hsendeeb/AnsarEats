<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'restaurant_id' => Restaurant::factory(),
            'delivery_address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'delivery_fee' => 0,
            'total' => $this->faker->randomFloat(2, 20, 200),
            'status' => 'pending',
        ];
    }
}
