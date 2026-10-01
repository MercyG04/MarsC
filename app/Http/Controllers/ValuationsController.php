<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth; 
use App\Enums\ValuationType;
use App\Enums\VehicleStatus;
use App\Http\Requests\StoreValuationRequest;
use App\Models\Valuation;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ValuationsController extends Controller
{
    public function index(Request $request): View
    {
      {
        $search = $request->string('q')->trim()->toString();

        $valuations = Valuation::query()
            ->with(['vehicle.client', 'createdBy'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('vehicle', function ($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%")
                      ->orWhere('chassis_number', 'like', "%{$search}%")
                      ->orWhere('logbook_number', 'like', "%{$search}%");
                });
            })
            ->latest('valuation_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
            return view('valuations.index', compact('valuations', 'search'));
      }
    } 
    
    

    public function create(Vehicle $vehicle): View
    {
        $vehicle->load('client');

        $suggestedType = $this->suggestValuationType($vehicle);

        return view('valuations.create', [
            'vehicle'       => $vehicle,
            'suggestedType' => $suggestedType,
            'types'         => ValuationType::cases(),
            'canOverride'   => Auth::user()?->isAdmin() ?? false,
        ]);
    }

    public function store(StoreValuationRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $valuation = $vehicle->valuations()->create(
            $request->validated() + [
                'created_by' => Auth::id(),
            ]
        );

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Valuation recorded. Vehicle is now eligible for policy issuance.');
    }

    public function show(Valuation $valuation): View
    {
        $valuation->load(['vehicle.client', 'createdBy']);

        return view('valuations.show', compact('valuation'));
    }

    protected function suggestValuationType(Vehicle $vehicle): ValuationType
    {
        return match ($vehicle->status) {
            VehicleStatus::PendingValuation => ValuationType::Initial,
            VehicleStatus::Valued,
            VehicleStatus::Insured           => ValuationType::Periodic,
            VehicleStatus::Retired           => ValuationType::Periodic,
        };
    }

}
