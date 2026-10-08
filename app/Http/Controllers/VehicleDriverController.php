<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleDriverRequest;
use App\Http\Requests\UpdateVehicleDriverRequest;
use App\Services\VerificationService;
use App\Models\Vehicle;
use App\Models\VehicleDriver;
use App\Enums\PolicyStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleDriverController extends Controller
{
    public function __construct(
        protected VerificationService $verification,
    ) {}    
public function create(Vehicle $vehicle): View
    {
        $vehicle->load('client');

        return view('vehicles.drivers.create', compact('vehicle'));
    }
    public function store(StoreVehicleDriverRequest $request, Vehicle $vehicle): RedirectResponse
    {
        // Verify with NTSA mock
        $verified = $this->verification->verifyDriver($request->input('dl_number'));

        if (! $verified) {
            return back()
            ->withInput()
                ->withErrors(['dl_number' => 'This driving licence could not be verified with NTSA.']);
        }

        $mismatches = $this->compareVerifiedData($request->validated(), $verified);

        if (! empty($mismatches)) {
            return back()
                ->withInput()
                ->withErrors($mismatches);
        }

        $driver = $vehicle->drivers()->create($request->validated());

        $this->enforceSinglePrimary($vehicle, $driver);

        return redirect()
            ->route('vehicles.drivers.show', [$vehicle, $driver])
            ->with('success', "Driver {$driver->name} added.");
    }
                
    public function show(Vehicle $vehicle, VehicleDriver $driver): View
    {
        abort_if($driver->vehicle_id !== $vehicle->id, 404);

        $driver->load('vehicle.client');

        return view('vehicles.drivers.show', compact('vehicle', 'driver'));
    }
    public function update(UpdateVehicleDriverRequest $request, Vehicle $vehicle, VehicleDriver $driver): RedirectResponse
    {
        abort_if($driver->vehicle_id !== $vehicle->id, 404);

        // The only mutable field is is_primary
        $driver->update($request->validated());

        $this->enforceSinglePrimary($vehicle, $driver);

        return redirect()
            ->route('vehicles.drivers.show', [$vehicle, $driver])
            ->with('success', "Primary driver updated.");
    }
    public function destroy(Vehicle $vehicle, VehicleDriver $driver): RedirectResponse
{
    abort_if($driver->vehicle_id !== $vehicle->id, 404);

    // Guard: can't delete a driver who's on an active policy
    $isOnActivePolicy = $driver->vehicle->policies()
        ->where('status', PolicyStatus::Active->value)
        ->exists();

    if ($isOnActivePolicy) {
        return back()->withErrors([
            'driver' => 'This driver is on an active policy. Use an endorsement to remove them.',
        ]);
    }

    if ($driver->is_primary) {
        return back()->withErrors([
            'driver' => 'Cannot remove the primary driver. Assign another driver as primary first.',
        ]);
    }

    $driver->delete();

    return redirect()
        ->route('vehicles.show', $vehicle)
        ->with('success', "Driver {$driver->name} removed.");
    }
    
    protected function enforceSinglePrimary(Vehicle $vehicle, VehicleDriver $driver): void
    {
        if ($driver->is_primary) {
            $vehicle->drivers()
                ->where('id', '!=', $driver->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }
        $hasPrimary = $vehicle->drivers()->where('is_primary', true)->exists();

        if (! $hasPrimary) {
            $driver->update(['is_primary' => true]);
        }
    }
    protected function compareVerifiedData(array $submitted, array $verified): array
    {
        $errors = [];

        if (isset($verified['name'])
            && strtoupper(trim($submitted['name'])) !== strtoupper(trim($verified['name']))) {
            $errors['name'] = 'Name does not match NTSA records.';
        }
        if (isset($verified['national_id'])
            && $submitted['national_id'] !== $verified['national_id']) {
            $errors['national_id'] = 'National ID does not match NTSA records.';
        }

        if (isset($verified['date_of_birth'])
            && $submitted['date_of_birth'] !== $verified['date_of_birth']) {
            $errors['date_of_birth'] = 'Date of birth does not match NTSA records.';
        }
        return $errors;
    }

}
