<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'User 1',
            'email'     => 'user1@example.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'User 2',
            'email'     => 'user2@example.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'User 3',
            'email'     => 'user3@example.com',
            'password'  => Hash::make('password'),
            'role'      => 'user',
            'is_active' => true,
        ]);
    }
}
