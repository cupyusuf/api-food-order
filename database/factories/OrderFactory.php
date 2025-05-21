<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'food_id' => Food::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'total_price' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
