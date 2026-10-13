<?php

namespace Database\Factories;

use App\Enums\VehicleStatus;
use App\Enums\VehicleUse;
use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'client_id'               => Client::factory(),
            'registration_number'     => strtoupper(fake()->unique()->bothify('???###?')),
            'chassis_number'          => strtoupper(fake()->unique()->bothify('CHASSIS########')),
            'logbook_number'          => strtoupper(fake()->unique()->bothify('LB-####')),
            'make'                    => fake()->randomElement(['Toyota', 'Nissan', 'Mazda', 'Subaru', 'Honda']),
            'model'                   => fake()->word(),
            'year_of_manufacture'     => fake()->numberBetween(2010, 2024),
            'initial_estimated_value' => fake()->numberBetween(500000, 5000000),
            'vehicle_use'             => VehicleUse::Personal,
            'status'                  => VehicleStatus::PendingValuation,
            'ntsa_verified'           => false,
        ];
    }

    public function valued(): static
    {
        return $this->state(fn () => ['status' => VehicleStatus::Valued]);
    }

    public function insured(): static
    {
        return $this->state(fn () => ['status' => VehicleStatus::Insured]);
    }
}