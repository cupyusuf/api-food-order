<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Food;
use App\Models\Order;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_place_an_order_with_multiple_items()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $food1 = Food::factory()->create(['price' => 10.00]);
        $food2 = Food::factory()->create(['price' => 20.00]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders', [
            'items' => [
                ['food_id' => $food1->id, 'quantity' => 2],
                ['food_id' => $food2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertCount(2, Order::all());
        $this->assertEquals(20.00, Order::first()->total_price);
        $this->assertEquals(20.00, Order::find(2)->total_price);
    }

    /** @test */
    public function it_can_list_orders_for_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $food = Food::factory()->create(['price' => 15.00]);
        Order::factory()->create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'quantity' => 2,
            'total_price' => 30.00,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }
}