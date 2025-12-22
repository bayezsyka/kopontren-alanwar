<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'owner@demo.local'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_OWNER,
                'ui_mode' => 'owner',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@demo.local'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_KASIR,
                'ui_mode' => 'kasir',
            ]
        );
    }
}
