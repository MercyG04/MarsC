<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@marsc.test'],
            [
                'name'         => 'MarsC Admin',
                'phone_number' => '0700000001',
                'password'     => Hash::make('password'),
                'role'         => UserRole::Admin,
            ]
        );

        User::updateOrCreate(
            ['email' => 'agent@marsc.test'],
            [
                'name'         => 'MarsC Agent',
                'phone_number' => '0700000002',
                'password'     => Hash::make('password'),
                'role'         => UserRole::Agent,
            ]
        );
    }
}