<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Food;

class FoodApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\Models\User::factory()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    private function withAuthHeaders(array $headers = []): array
    {
        return array_merge($headers, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);
    }

    public function test_can_get_all_foods()
    {
        Food::factory()->count(3)->create();

        $response = $this->getJson('/api/foods');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_food()
    {
        $data = [
            'name' => 'Pizza',
            'description' => 'Delicious cheese pizza',
            'price' => 9.99
        ];

        $response = $this->withHeaders($this->withAuthHeaders())
            ->postJson('/api/foods', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }

    public function test_can_get_single_food()
    {
        $food = Food::factory()->create();

        $response = $this->getJson("/api/foods/{$food->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $food->name]);
    }

    public function test_can_update_food()
    {
        $food = Food::factory()->create();

        $data = [
            'name' => 'Updated Food',
            'price' => 12.99
        ];

        $response = $this->withHeaders($this->withAuthHeaders())
            ->putJson("/api/foods/{$food->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment($data);
    }

    public function test_can_delete_food()
    {
        $food = Food::factory()->create();

        $response = $this->withHeaders($this->withAuthHeaders())
            ->deleteJson("/api/foods/{$food->id}");

        $response->assertStatus(204);
    }
}