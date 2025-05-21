<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }

    /** @test */
    public function it_can_login_a_user()
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);
    }
}
