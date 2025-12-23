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
            ['email' => 'farrosy6@alanwarpakijangan.com'],
            [
                'name' => 'Owner',
                'password' => Hash::make('elfarros'),
                'role' => User::ROLE_OWNER,
                'ui_mode' => 'owner',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@alanwarpakijangan.com'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('pakijangan'),
                'role' => User::ROLE_KASIR,
                'ui_mode' => 'kasir',
            ]
        );
    }
}
