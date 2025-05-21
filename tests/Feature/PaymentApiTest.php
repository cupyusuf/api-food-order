<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Food;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_initiate_payment_for_an_order()
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'address' => '123 Test Street',
        ]);

        $this->actingAs($user);

        $food = Food::factory()->create(['price' => 10000]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'food_id' => $food->id,
            'quantity' => 2,
            'total_price' => 20000,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/pay");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'snap_token',
            ]);
    }
}