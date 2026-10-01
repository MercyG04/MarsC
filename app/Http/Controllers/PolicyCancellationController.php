<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCancellationRequest;
use App\Models\Policy;
use App\Services\CancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PolicyCancellationController extends Controller
{
    public function __construct(
        protected CancellationService $cancellations,
    ) {}

    /**
     * Show the cancellation form.
     */
    public function create(Policy $policy): View
    {
        // Guard: only active policies can be cancelled
        abort_if(
            $policy->status !== \App\Enums\PolicyStatus::Active,
            403,
            'Only active policies can be cancelled.'
        );

        // Guard: can't double-cancel
        abort_if(
            $policy->cancellation_status !== null,
            403,
            'Cancellation already in progress.'
        );

        return view('policies.cancel', compact('policy'));
    }

    /**
     * Process the cancellation request.
     */
    public function store(StoreCancellationRequest $request, Policy $policy): RedirectResponse
    {
        try {
            $this->cancellations->requestCancellation(
                $policy,
                $request->validated('reason'),
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()
            ->route('policies.show', $policy)
            ->with('success', 'Cancellation processed. Cover has stopped. Awaiting certificate surrender.');
    }

    /**
     * Mark the certificate as surrendered. Finalises the cancellation.
     */
    public function markCertificateSurrendered(Policy $policy): RedirectResponse
    {
        try {
            $this->cancellations->markCertificateSurrendered($policy);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('policies.show', $policy)
            ->with('success', 'Certificate surrendered. Cancellation complete.');
    }
}