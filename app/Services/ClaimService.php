<?php

namespace App\Services;

use App\Enums\ClaimDocumentType;
use App\Enums\ClaimStatus;
use App\Enums\PolicyStatus;
use App\Enums\RejectionReason;
use App\Models\Claim;
use App\Models\Policy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClaimService
{
    /**
     * File a new claim against an active policy.
     */
    public function file(Policy $policy, array $data): Claim
    {
        if ($policy->status !== PolicyStatus::Active) {
            throw new \RuntimeException('Only active policies can receive claims.');
        }

        return DB::transaction(function () use ($policy, $data) {

            $claim = Claim::create([
                'claim_number'        => $this->nextNumber(),
                'policy_id'           => $policy->id,
                'incident_date'       => $data['incident_date'],
                'description'         => $data['description'],
                'police_abstract_ref' => $data['police_abstract_ref'] ?? null,
                'location'            => $data['location'] ?? null,
                'driver_id'           => $data['driver_id'] ?? null,
                'claimed_amount'      => $data['claimed_amount'],
                'claim_type'          => $data['claim_type'],
                'status'              => ClaimStatus::Submitted,
                'reported_at'         => now(),
                'created_by'          => Auth::id(),
            ]);

            return $claim;
        });
    }
    

    /**
     * Acknowledge a claim (AKI requires within 24 hours).
     */
    public function acknowledge(Claim $claim): Claim
    {
        $this->guardTransition($claim, ClaimStatus::Acknowledged);

        $claim->update([
            'status'          => ClaimStatus::Acknowledged,
            'acknowledged_at' => now(),
        ]);

        return $claim->fresh();
    }

    /**
     * Move a claim into review. Requires all intake documents.
     */
    public function beginReview(Claim $claim, int $assignedTo): Claim
    {
        $this->guardTransition($claim, ClaimStatus::UnderReview);

        // Check that required documents have been received
        $missing = $this->missingRequiredDocuments($claim);

        if (! empty($missing)) {
            throw new \RuntimeException(
                'Cannot begin review — missing documents: ' . implode(', ', $missing)
            );
        }

        $claim->update([
            'status'      => ClaimStatus::UnderReview,
            'assigned_to' => $assignedTo,
        ]);

        return $claim->fresh();
    }

    /**
     * Record the assessor's findings.
     */
    public function recordAssessment(Claim $claim, array $data): Claim
    {
        $this->guardTransition($claim, ClaimStatus::Assessed);

        $claim->update([
            'status'            => ClaimStatus::Assessed,
            'assessor_id'       => $data['assessor_id'] ?? Auth::id(),
            'assessed_amount'   => $data['assessed_amount'],
            'adjustment_type'   => $data['adjustment_type'],
            'salvage_value'     => $data['salvage_value'] ?? 0,
            'assessed_at'       => now(),
        ]);

        return $claim->fresh();
    }

    /**
     * Approve the claim. Computes deductible and net payout.
     */
    public function approve(Claim $claim, float $approvedAmount, ?float $salvageValue = null): Claim
    {
        $this->guardTransition($claim, ClaimStatus::Approved);

        $deductible = (float) $claim->policy->deductible_amount;

        if ($approvedAmount <= $deductible) {
            throw new \RuntimeException(
                'Approved amount does not exceed the policy deductible. Claim is not payable.'
            );
        }

        $claim->update([
            'status'             => ClaimStatus::Approved,
            'approved_amount'    => $approvedAmount,
            'deductible_applied' => $deductible,
            'salvage_value'      => $salvageValue ?? $claim->salvage_value ?? 0,
            'approved_at'        => now(),
        ]);

        return $claim->fresh();
    }

    /**
     * Reject the claim.
     */
    public function reject(Claim $claim, RejectionReason $reason,  ?string $notes = null): Claim
    {
        if ($claim->status->isFinal()) {
            throw new \RuntimeException('Claim is already in a final state.');
        }
        if ($reason->requiresEvidence() && empty($notes)) {
        throw new \RuntimeException(
            'Fraud-based rejections require documented evidence in the notes.'
        );
    }

        $claim->update([
            'status'            => ClaimStatus::Rejected,
            'rejection_reason'  =>  $reason->value,
            'rejection_notes'   => $notes,
            'rejected_at'       => now(),
        ]);

        return $claim->fresh();
    }

    /**
     * Record payment and close the claim.
     */
    public function markPaid(Claim $claim): Claim
    {
        $this->guardTransition($claim, ClaimStatus::Paid);

        $claim->update([
            'status'  => ClaimStatus::Paid,
            'paid_at' => now(),
        ]);

        return $claim->fresh();
    }

    /* ─────────────────────────────────────────────────────
     *  HELPERS
     * ───────────────────────────────────────────────────── */

    /**
     * Throws if the transition is illegal per the enum's state machine.
     */
    protected function guardTransition(Claim $claim, ClaimStatus $target): void
    {
        if (! $claim->status->canTransitionTo($target)) {
            throw new \RuntimeException(
                "Cannot transition claim from '{$claim->status->label()}' to '{$target->label()}'."
            );
        }
    }

    /**
     * Returns an array of required document types not yet on the claim.
     */
    protected function missingRequiredDocuments(Claim $claim): array
    {
        $required = ClaimDocumentType::requiredForIntake();
        $requiredValues = array_map(fn ($t) => $t->value, $required);

        $received = $claim->documents()
            ->pluck('type')
            ->map(fn ($t) => $t->value)
            ->toArray();

        return array_values(array_diff($requiredValues, $received));
    }

    /**
     * Generate the next claim number for the current year.
     * Format: CLM-YYYY-NNNNN
     */
    protected function nextNumber(): string
    {
        $prefix = 'CLM-' . now()->year . '-';

        $latest = Claim::withTrashed()
            ->where('claim_number', 'like', "{$prefix}%")
            ->orderByDesc('claim_number')
            ->lockForUpdate()
            ->first();

        $next = $latest
            ? ((int) substr($latest->claim_number, -5)) + 1
            : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}