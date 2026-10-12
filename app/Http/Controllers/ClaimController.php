<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\ClaimStatus;
use App\Enums\ClaimType;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\AssessClaimRequest;
use App\Http\Requests\AcknowledgeClaimRequest;
use App\Http\Requests\AssignClaimRequest;
use App\Http\Requests\MarkClaimPaidRequest;
use App\Http\Requests\RejectClaimRequest;
use App\Http\Requests\ApproveClaimRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Claim;
use App\Models\Policy;
use App\Services\ClaimService;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function __construct(
        protected ClaimService $claims,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $claims = Claim::query()
            ->with(['policy.client', 'policy.vehicle', 'driver', 'assignedTo'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('claim_number', 'like', "%{$search}%")
                      ->orWhere('police_abstract_ref', 'like', "%{$search}%")
                      ->orWhereHas('policy', function ($pq) use ($search) {
                          $pq->where('policy_number', 'like', "%{$search}%")
                             ->orWhereHas('client', function ($cq) use ($search) {
                                 $cq->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                             })
                             ->orWhereHas('vehicle', function ($vq) use ($search) {
                                $vq->where('registration_number', 'like', "%{$search}%");
                             });
                      });
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('reported_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('claims.index', [
            'claims'   => $claims,
            'search'   => $search,
            'status'   => $status,
            'statuses' => ClaimStatus::cases(),
          ]);
    }
    
    public function create(Policy $policy): View
    {
        // Guard: only active policies can receive claims
        abort_if(
            $policy->status !== \App\Enums\PolicyStatus::Active,
            403,
            'Only active policies can receive claims.'
        );

        $policy->load(['client', 'vehicle.drivers']);

        return view('claims.create', [
            'policy'     => $policy,
            'claimTypes' => ClaimType::cases(),
        ]);
    }
    public function store(StoreClaimRequest $request, Policy $policy): RedirectResponse
    {
        $claim = $this->claims->file($policy, $request->validated());

        return redirect()
            ->route('claims.show', $claim)
            ->with('success', "Claim {$claim->claim_number} filed successfully.");
    }
     public function show(Claim $claim): View
    {
        $claim->load([
            'policy.client',
            'policy.vehicle',
            'driver',
            'assignedTo',
            'assessor',
            'createdBy',
            'documents',
        ]);

        return view('claims.show', compact('claim'));
    }
    /**
 * Acknowledge a claim. No input required.
 */
public function acknowledge(AcknowledgeClaimRequest $request, Claim $claim): RedirectResponse
{
    try {
        $this->claims->acknowledge($claim);
    } catch (\RuntimeException $e) {
        return back()->withErrors(['transition' => $e->getMessage()]);
    }

    return redirect()
        ->route('claims.show', $claim)
        ->with('success', "Claim {$claim->claim_number} acknowledged.");
}

/**
 * Begin review. Requires a claims officer to assign and all intake documents.
 */
public function beginReview(AssignClaimRequest $request, Claim $claim): RedirectResponse
    {
    try {
        $this->claims->beginReview($claim, $request->integer('assigned_to'));
    } catch (\RuntimeException $e) {
        return back()->withErrors(['transition' => $e->getMessage()]);
    }

    return redirect()
        ->route('claims.show', $claim)
        ->with('success', "Claim {$claim->claim_number} moved to review.");
    }

/**
 * Record the assessor's findings.
 */
public function recordAssessment(AssessClaimRequest $request, Claim $claim): RedirectResponse
    {
    try {
        $this->claims->recordAssessment($claim, $request->validated());
    } catch (\RuntimeException $e) {
        return back()->withErrors(['transition' => $e->getMessage()]);
    }

    return redirect()
        ->route('claims.show', $claim)
        ->with('success', "Assessment recorded for claim {$claim->claim_number}.");
    }

    /**
 * Approve a claim — commits to a payout.
 * Underwriter or admin only.
 */
public function approve(ApproveClaimRequest $request, Claim $claim): RedirectResponse
{
    try {
        $this->claims->approve(
            $claim,
            (float) $request->input('approved_amount'),
            $request->filled('salvage_value') ? (float) $request->input('salvage_value') : null,
        );
    } catch (\RuntimeException $e) {
        return back()->withErrors(['transition' => $e->getMessage()]);
    }

    return redirect()
        ->route('claims.show', $claim)
        ->with('success', "Claim {$claim->claim_number} approved.");
}

/**
 * Reject a claim — denies liability with a documented reason.
 * Underwriter or admin only.
 */
public function reject(RejectClaimRequest $request, Claim $claim): RedirectResponse
{
    try {
        $this->claims->reject(
            $claim,
            \App\Enums\RejectionReason::from($request->input('rejection_reason')),
            $request->input('rejection_notes'),
        );
    } catch (\RuntimeException $e) {
        return back()->withErrors(['transition' => $e->getMessage()]);
    }

    return redirect()
        ->route('claims.show', $claim)
        ->with('success', "Claim {$claim->claim_number} rejected.");
}

/**
 * Mark the claim as paid. Final state.
 * Admin only.
 */
public function markPaid(MarkClaimPaidRequest $request, Claim $claim): RedirectResponse
{
    try {
        $this->claims->markPaid($claim);
    } catch (\RuntimeException $e) {
        return back()->withErrors(['transition' => $e->getMessage()]);
    }

    return redirect()
        ->route('claims.show', $claim)
        ->with('success', "Claim {$claim->claim_number} marked as paid.");
}

}
