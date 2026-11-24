<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin Kelompok',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name'     => 'User Kelompok',
                'password' => Hash::make('password123'),
                'role'     => 'user',
            ]
        );
        User::updateOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name'     => 'User Kelompok 2',
                'password' => Hash::make('password123'),
                'role'     => 'user',
            ]
        );
    }
}
