<?php

use App\Enums\VehicleStatus;
use App\Enums\VehicleUse;
use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('creates a vehicle linked to a client', function () {
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
        'status'                  => VehicleStatus::PendingValuation,
    ]);

    expect($vehicle->client->id)->toBe($client->id)
        ->and($vehicle->status)->toBe(VehicleStatus::PendingValuation);
});

it('uppercases registration and chassis numbers on save', function () {
    $client = Client::factory()->create();

    $vehicle = $client->vehicles()->create([
        'registration_number'     => 'kdb345',
        'chassis_number'          => 'chassis123456',
        'logbook_number'          => 'logbook123',
        'make'                    => 'Toyota',
        'model'                   => 'Hilux',
        'year_of_manufacture'     => 2020,
        'initial_estimated_value' => 2000000,
        'vehicle_use'             => VehicleUse::Personal,
        'status'                  => VehicleStatus::PendingValuation,
    ]);

    expect($vehicle->registration_number)->toBe('KDB345')
        ->and($vehicle->chassis_number)->toBe('CHASSIS123456');
});

it('prevents changing the registration number after creation', function () {
    $vehicle = Vehicle::factory()->create(['registration_number' => 'KDB345']);

    expect(fn () => $vehicle->update(['registration_number' => 'XYZ999']))
        ->toThrow(\RuntimeException::class);
});

it('prevents changing the initial estimated value after creation', function () {
    $vehicle = Vehicle::factory()->create(['initial_estimated_value' => 2000000]);

    expect(fn () => $vehicle->update(['initial_estimated_value' => 5000000]))
        ->toThrow(\RuntimeException::class);
});

it('verifies a vehicle with NTSA and updates the verification flag', function () {
    // Fake the NTSA TIMS response
    Http::fake([
        '*/verify' => Http::response([
            'ok'   => true,
            'source' => 'NTSA-TIMS',
            'data' => [
                'registration_number' => 'KDA777Z',
                'chassis_number'      => 'NEWCHASSIS123',
                'make'                => 'Toyota',
                'model'               => 'Land Cruiser',
                'year_of_manufacture' => 2022,
                'stolen'              => false,
                'duty_paid'           => true,
            ],
        ]),
    ]);

    $client = Client::factory()->create();
    $service = app(\App\Services\VehicleOnboardingService::class);

    $vehicle = $service->onboard($client, [
        'registration_number'     => 'KDA777Z',
        'chassis_number'          => 'NEWCHASSIS123',
        'logbook_number'          => 'NEWLOGBOOK123',
        'make'                    => 'Toyota',
        'model'                   => 'Land Cruiser',
        'year_of_manufacture'     => 2022,
        'initial_estimated_value' => 5000000,
        'vehicle_use'             => VehicleUse::Personal,
    ]);

    expect($vehicle->ntsa_verified)->toBeTrue()
        ->and($vehicle->ntsa_verified_at)->not->toBeNull()
        ->and($vehicle->status)->toBe(VehicleStatus::PendingValuation);
});

it('allows a vehicle to receive a policy only when valued', function () {
    $pending = Vehicle::factory()->create(['status' => VehicleStatus::PendingValuation]);
    $valued  = Vehicle::factory()->valued()->create();
    $insured = Vehicle::factory()->insured()->create();

    expect($pending->canReceivePolicy())->toBeFalse()
        ->and($valued->canReceivePolicy())->toBeTrue()
        ->and($insured->canReceivePolicy())->toBeFalse();
});