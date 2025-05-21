<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Yusuf Supriadi',
            'email' => 'yusuf@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        Role::create(['name' => 'buyer']);
        Role::create(['name' => 'staff']);

        $staffRole = Role::where('name', 'staff')->first();
        $user = User::where('email', 'yusuf@gmail.com')->first();
        $user->role()->associate($staffRole);
        $user->save();
    }
}