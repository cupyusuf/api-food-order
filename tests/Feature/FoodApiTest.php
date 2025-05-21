<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Food;
use App\Models\User;

class FoodApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_foods_without_authentication()
    {
        Food::factory()->count(3)->create();

        $response = $this->getJson('/api/foods');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_cannot_create_food_without_authentication()
    {
        $response = $this->postJson('/api/foods', [
            'name' => 'Pizza',
            'description' => 'Delicious cheese pizza',
            'price' => 9.99,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_food_with_authentication()
    {
        $role = \App\Models\Role::firstOrCreate(['name' => 'staff']);
        $user = User::factory()->create(['role_id' => $role->id]); // Mengaitkan pengguna dengan peran 'staff'
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/foods', [
            'name' => 'Pizza',
            'description' => 'Delicious cheese pizza',
            'price' => 9.99,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'price',
                'created_at',
                'updated_at'
            ]);
    }

    /** @test */
    public function it_can_update_food_with_authentication()
    {
        $role = \App\Models\Role::firstOrCreate(['name' => 'staff']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $food = Food::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/foods/' . $food->id, [
            'name' => 'Updated Pizza',
            'price' => 12.99,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Pizza',
                'price' => 12.99,
            ]);
    }

    /** @test */
    public function it_can_delete_food_with_authentication()
    {
        $role = \App\Models\Role::firstOrCreate(['name' => 'staff']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $food = Food::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/foods/' . $food->id);

        $response->assertStatus(204);
    }
}