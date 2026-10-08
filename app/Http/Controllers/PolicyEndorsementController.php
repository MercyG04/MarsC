<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Http\Requests\StoreEndorsementRequest;
use App\Models\AddOn;
use App\Models\Policy;
use App\Models\Endorsement;
use App\Services\EndorsementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PolicyEndorsementController extends Controller
{
    public function __construct(
        protected EndorsementService $endorsements,
    ) {}
    public function create(Policy $policy): View
    {
        abort_if(
            $policy->status !== PolicyStatus::Active,
            403,
            'Only active policies can be endorsed.'
        );

        $policy->load(['client', 'vehicle.drivers', 'addOns']);

        return view('policies.endorse', [
            'policy' => $policy,
            'types'  => PolicyType::cases(),
            'addOns' => AddOn::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
    public function store(StoreEndorsementRequest $request, Policy $policy): RedirectResponse
    {
        try {
            $endorsement = $this->endorsements->request(
                $policy,
                $request->validated(),
                $request->validated('reason'),
            );
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['endorsement' => $e->getMessage()]);
        }
        return redirect()
            ->route('policies.show', $policy)
            ->with('success', "Endorsement {$endorsement->endorsement_number} recorded successfully.");
    }
    public function show(Policy $policy, Endorsement $endorsement): View
    {
    // Guard: the endorsement must belong to the policy in the URL
    abort_if(
        $endorsement->policy_id !== $policy->id,
        404,
        'Endorsement not found for this policy.'
    );

    $endorsement->load(['policy.client', 'policy.vehicle', 'createdBy']);

    return view('policies.endorsement-show', compact('policy', 'endorsement'));
    }
}
