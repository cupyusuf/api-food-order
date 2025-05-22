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
        $orders = \App\Models\Order::all();
        $this->assertCount(1, $orders);
        $order = $orders->first();
        $this->assertEquals(40.00, $order->total_price);
        $this->assertCount(2, $order->items);
        $this->assertEquals($food1->id, $order->items[0]->food_id);
        $this->assertEquals($food2->id, $order->items[1]->food_id);
    }

    /** @test */
    public function it_can_list_orders_for_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $food1 = Food::factory()->create(['price' => 15.00]);
        $food2 = Food::factory()->create(['price' => 5.00]);
        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'total_price' => 35.00,
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $food1->id,
            'quantity' => 2,
            'price' => 15.00,
            'total_price' => 30.00,
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'food_id' => $food2->id,
            'quantity' => 1,
            'price' => 5.00,
            'total_price' => 5.00,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'total_price' => 35.00
        ]);
    }
}
