<?php

namespace Tests\Feature;

use App\Enums\VehicleUse;
use app\Enums\VehicleStatus;
use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_be_created_with_a_vehicle(): void
    {
        $client = Client::create([
            'first_name'         => 'Jane',
            'last_name'          => 'Wanjiru',
            'other_names'        => 'Njeri',
            'national_id'        => '12345678',
            'kra_pin'            => 'A012345678Z',
            'phone_number'       => '0712345678',
            'email'              => 'jane@example.com',
            'date_of_birth'      => '1990-05-10',
            'occupation'         => 'Engineer',
            'county'             => 'Nairobi',
            'physical_location'  => 'Westlands',
            'gender'             => 'female',
            'driving_experience' => 8,
            'consent_given'      => true,
        ]);

        $vehicle = $client->vehicles()->create([
            'registration_number'     => 'KDB345',
            'chassis_number'          => 'CHASSIS123456',
            'logbook_number'          => 'LOGBOOK123',
            'make'                    => 'Mercedes Benz',
            'model'                   => 'G 63 AMG',
            'year_of_manufacture'     => 2020,
            'initial_estimated_value' => 2000000,
            'vehicle_use'             => VehicleUse::Personal,
            'status'                   => VehicleStatus::PendingValuation,
            'ntsa_verified'           => true,
            'ntsa_verified_at'        => now(),
        ]);

        $this->assertDatabaseHas('clients', [
            'national_id' => '12345678',
            'consent_given' => true,
        ]);

        $this->assertDatabaseHas('vehicles', [
            'registration_number' => 'KDB345',
            'client_id' => $client->id,
        ]);

        $this->assertEquals(1, $client->vehicles()->count());
        $this->assertEquals($client->id, $vehicle->client->id);
    }
}