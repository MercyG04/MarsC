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
                'password'     => Hash::make('Demo123!'),
                'role'         => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'agent@marsc.test'],
            [
                'name'         => 'Andrew Agent',
                'phone_number' => '0700000002',
                'password'     => Hash::make('Demo456!'),
                'role'         => UserRole::Agent,
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'underwriter@marsc.test'],
            [
                'name'              => 'Mary Underwriter',
                'phone_number'      => '0700000002',
                'password'          => Hash::make('Demo1234!'),
                'role'              => UserRole::Underwriter,
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'claims@marsc.test'],
            [
                'name'              => 'Kevin Claims',
                'phone_number'      => '0700000003',
                'password'          => Hash::make('Demo1234!'),
                'role'              => UserRole::ClaimsOfficer,
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['email' => 'agent1@marsc.test'],
            [
                'name'              => 'Alice Agent',
                'phone_number'      => '0700000004',
                'password'          => Hash::make('Demo1234!'),
                'role'              => UserRole::Agent,
                'email_verified_at' => now(),
            ]
        );
    }
}