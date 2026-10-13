<?php

use App\Enums\VehicleUse;
use App\Models\Client;
use App\Models\Vehicle;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('creates a client with all required fields', function () {
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

    expect($client->first_name)->toBe('Jane')
        ->and($client->last_name)->toBe('Wanjiru')
        ->and($client->full_name)->toBe('Jane Njeri Wanjiru')
        ->and($client->consent_given)->toBeTrue()
        ->and($client->consent_given_at)->not->toBeNull();

    $this->assertDatabaseHas('clients', [
        'national_id' => '12345678',
        'consent_given' => true,
    ]);
});

it('auto-sets consent_given_at when consent is given', function () {
    $client = Client::create([
        'first_name'         => 'Test',
        'last_name'          => 'User',
        'national_id'        => '99999999',
        'kra_pin'            => 'A999999999Z',
        'phone_number'       => '0700000000',
        'email'              => 'test@example.com',
        'date_of_birth'      => '1990-01-01',
        'county'             => 'Nairobi',
        'physical_location'  => 'CBD',
        'gender'             => 'male',
        'driving_experience' => 5,
        'consent_given'      => true,
    ]);

    expect($client->consent_given_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

it('enriches client data from the verification service', function () {
    // Fake the mock server response
    Http::fake([
        '*/verify' => Http::response([
            'ok'   => true,
            'source' => 'IPRS',
            'data' => [
                'national_id'   => '12345678',
                'first_name'    => 'Jane',
                'last_name'     => 'Wanjiru',
                'other_names'   => 'Njeri',
                'kra_pin'       => 'A012345678Z',
                'date_of_birth' => '1990-05-10',
                'gender'        => 'female',
            ],
        ]),
    ]);

    $service = app(\App\Services\ClientKycService::class);

    $client = $service->onboard([
        'first_name'         => 'IGNORED',   // will be overwritten by IPRS
        'last_name'          => 'IGNORED',
        'national_id'        => '12345678',
        'kra_pin'            => 'IGNORED',
        'phone_number'       => '0712345678',
        'email'              => 'jane@example.com',
        'date_of_birth'      => '1900-01-01',
        'occupation'         => 'Engineer',
        'county'             => 'Nairobi',
        'physical_location'  => 'Westlands',
        'gender'             => 'male',
        'driving_experience' => 0,
        'consent_given'      => true,
    ]);

    // IPRS values should override the submitted values
    expect($client->first_name)->toBe('Jane')
        ->and($client->last_name)->toBe('Wanjiru')
        ->and($client->kra_pin)->toBe('A012345678Z')
        ->and($client->gender)->toBe('female');
});

it('links a vehicle to a client', function () {
    $client = Client::factory()->create();

    $vehicle = $client->vehicles()->create([
        'registration_number'     => 'KDB345',
        'chassis_number'          => 'CHASSIS123456',
        'logbook_number'          => 'LOGBOOK123',
        'make'                    => 'Mercedes Benz',
        'model'                   => 'G 63 AMG',
        'year_of_manufacture'     => 2020,
        'initial_estimated_value' => 2000000,
        'vehicle_use'             => VehicleUse::Personal,
        'status'                  => \App\Enums\VehicleStatus::PendingValuation,
    ]);

    expect($client->vehicles()->count())->toBe(1)
        ->and($vehicle->client->id)->toBe($client->id);

    $this->assertDatabaseHas('vehicles', [
        'registration_number' => 'KDB345',
        'client_id'           => $client->id,
    ]);
});