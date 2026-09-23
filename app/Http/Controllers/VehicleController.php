<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Client;
use App\Models\Vehicle;
use App\Services\VehicleOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class VehicleController extends Controller
{
    public function __construct(
        protected VehicleOnboardingService $onboarding,
    ) {}

    /**
     * Show the vehicle onboarding form for a given client.
     */
    public function create(Client $client): View
    {
        return view('vehicles.create', compact('client'));
    }
    public function store(StoreVehicleRequest $request, Client $client): RedirectResponse
    {
        $vehicle = $this->onboarding->onboard($client, $request->validated());

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', "Vehicle {$vehicle->registration_number} onboarded. Awaiting valuation.");
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load(['client', 'valuations']);

        return view('vehicles.show', compact('vehicle'));
    }
}
