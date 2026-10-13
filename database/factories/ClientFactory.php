<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'first_name'         => fake()->firstName(),
            'last_name'          => fake()->lastName(),
            'other_names'        => null,
            'national_id'        => fake()->unique()->numerify('########'),
            'kra_pin'            => 'A' . fake()->numerify('#########') . 'Z',
            'phone_number'       => fake()->numerify('07########'),
            'email'              => fake()->unique()->safeEmail(),
            'date_of_birth'      => fake()->date('Y-m-d', '-25 years'),
            'occupation'         => fake()->jobTitle(),
            'county'             => 'Nairobi',
            'physical_location'  => fake()->streetName(),
            'gender'             => fake()->randomElement(['male', 'female']),
            'driving_experience' => fake()->numberBetween(1, 30),
            'consent_given'      => true,
            'consent_given_at'   => now(),
        ];
    }
}