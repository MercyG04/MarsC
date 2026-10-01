<?php

namespace App\Http\Controllers;

use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Http\Requests\StorePolicyRequest;
use App\Models\AddOn;
use App\Models\Policy;
use App\Models\Vehicle;
use App\Services\PolicyIssuanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolicyController extends Controller
{
    public function __construct(
        protected PolicyIssuanceService $issuance,
    ) {}

    /**
     * List all policies with search and status filter.
     *
     * Search: policy number, client name, vehicle registration
     * Filter: status (active, pending, cancelled, expired)
     */
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $policies = Policy::query()
            ->with(['client', 'vehicle'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('policy_number', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($cq) use ($search) {
                          $cq->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%")
                             ->orWhere('national_id', 'like', "%{$search}%");
                      })
                      ->orWhereHas('vehicle', function ($vq) use ($search) {
                          $vq->where('registration_number', 'like', "%{$search}%");
                      });
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('issued_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('policies.index', [
            'policies' => $policies,
            'search'   => $search,
            'status'   => $status,
            'statuses' => PolicyStatus::cases(),
        ]);
    }

    /**
     * Show the policy issuance form for a vehicle.
     */
    public function create(Vehicle $vehicle): View
    {
        abort_if(
            ! $vehicle->canReceivePolicy(),
            403,
            'This vehicle is not yet eligible for a policy. It must be valued first.'
        );

        return view('policies.create', [
            'vehicle' => $vehicle->load('client'),
            'types'   => PolicyType::cases(),
            'addOns'  => AddOn::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Persist a new policy.
     */
    public function store(StorePolicyRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $policy = $this->issuance->issue($vehicle, $request->validated());

        return redirect()
            ->route('policies.show', $policy)
            ->with('success', "Policy {$policy->policy_number} issued successfully.");
    }

    /**
     * Show a single policy with all related data.
     */
    public function show(Policy $policy): View
    {
        $policy->load([
            'client',
            'vehicle',
            'addOns',
            'endorsements',
            'cancelledBy',
        ]);

        return view('policies.show', compact('policy'));
    }
}