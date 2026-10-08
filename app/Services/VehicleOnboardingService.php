<?php

namespace App\Services;

use App\Enums\VehicleStatus;
use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\VehicleUse;

class VehicleOnboardingService
{
    public function __construct(
        protected VerificationService $verification,
    ) {}

    /**
     * Onboard a new vehicle for a client.
     *
     * @param  Client $client  The owning client.
     * @param  array  $data    Validated vehicle data from the form request.
     * @return Vehicle
     */
    public function onboard(Client $client, array $data): Vehicle
    {
        return DB::transaction(function () use ($client, $data) {

            $vehicle = $client->vehicles()->create([
                'registration_number'     => $data['registration_number'],
                'chassis_number'          => $data['chassis_number'],
                'logbook_number'          => $data['logbook_number'],
                'make'                    => $data['make'],
                'model'                   => $data['model'],
                'year_of_manufacture'     => $data['year_of_manufacture'],
                'initial_estimated_value' => $data['initial_estimated_value'],
                'vehicle_use'             => $data['vehicle_use'],
                'status'                  => VehicleStatus::PendingValuation,
                'ntsa_verified'           => false,
                'ntsa_verified_at'        => null,
            ]);

            $this->verifyWithNtsa($vehicle);
            $this->autoCreateOwnerAsDriver($vehicle);

           return $vehicle->fresh(['drivers']);
        });
    }

    /**
     * Verify the vehicle against NTSA (mocked). Non-fatal — if the mock
     * is down or returns nothing, we proceed without verification.
     */
    protected function verifyWithNtsa(Vehicle $vehicle): void
    {
    try {
        $result = $this->verification->verifyVehicle($vehicle->registration_number);

        if (! $result) {
            return; // verification failed silently — stays unverified
        }

        $vehicle->update([
            'ntsa_verified'    => true,
            'ntsa_verified_at' => now(),
        ]);

        // Cross-check chassis — mismatch is a fraud signal
        if (
            isset($result['chassis_number']) &&
            strtoupper((string) $result['chassis_number']) !== strtoupper((string) $vehicle->chassis_number)
        ) {
            Log::warning('Chassis mismatch between user input and NTSA TIMS', [
                'vehicle_id'   => $vehicle->id,
                'db_chassis'   => $vehicle->chassis_number,
                'ntsa_chassis' => $result['chassis_number'],
            ]);
        }
    } catch (\Throwable $e) {
        Log::warning('NTSA verification failed during onboarding', [
            'vehicle_id' => $vehicle->id,
            'error'      => $e->getMessage(),
        ]);
    }
    }

    protected function autoCreateOwnerAsDriver(Vehicle $vehicle): void
{
    // Only for Personal use
    if ($vehicle->vehicle_use !== VehicleUse::Personal) {
        return;
    }

    $client = $vehicle->client;

    $vehicle->drivers()->create([
        'name'                => "{$client->first_name} {$client->last_name}",
        'national_id'         => $client->national_id,
        'dl_number'           => 'PENDING',      // TODO: query NTSA for the owner's DL
        'dl_class'            => 'B',            // Personal cars use class B
        'date_of_birth'       => $client->date_of_birth,
        'driving_experience'  => $client->driving_experience,
        'is_primary'          => true,
    ]);
}
}