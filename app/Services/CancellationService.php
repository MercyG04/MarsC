<?php

namespace App\Services;

use App\Enums\PolicyCancellationStatus;
use App\Enums\PolicyStatus;
use App\Models\Policy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancellationService
{
    /**
     * Short-period refund factor.
     * IRA  short-period rates; 15% admin deduction .
     */
    const SHORT_PERIOD_ADMIN_FACTOR = 0.85;

    /**
     * Request cancellation of a policy.
     */
    public function requestCancellation(Policy $policy, string $reason): Policy
    {
        if ($policy->status !== PolicyStatus::Active) {
            throw new \RuntimeException('Only active policies can be cancelled.');
        }

        if ($policy->cancellation_status !== null) {
            throw new \RuntimeException('Cancellation already in progress.');
        }

        return DB::transaction(function () use ($policy, $reason) {
            $refund = $this->calculateRefund($policy);

            $policy->update([
                'cancellation_status'       => PolicyCancellationStatus::CertificatePending,
                'cancellation_requested_at' => now(),
                'cancellation_effective_at' => now(),
                'refund_amount'             => $refund,
                'cancellation_reason'       => $reason,
                'cancelled_by'              => Auth::id(),
                'status'                    => PolicyStatus::Cancelled,
            ]);

            return $policy->fresh();
        });
    }

    /**
     * Mark the certificate as surrendered. Finalizes the cancellation.
     */
    public function markCertificateSurrendered(Policy $policy): Policy
    {
        if ($policy->cancellation_status !== PolicyCancellationStatus::CertificatePending) {
            throw new \RuntimeException('No pending cancellation for this policy.');
        }

        $policy->update([
            'cancellation_status'        => PolicyCancellationStatus::Completed,
            'certificate_surrendered_at' => now(),
        ]);

        return $policy->fresh();
    }

    /**
     * Calculate refund on a short-period basis.
     *
     * remaining_days / total_days × gross_premium × admin_factor
     */
    protected function calculateRefund(Policy $policy): float
    {
        $totalDays     = $policy->start_date->diffInDays($policy->end_date);
        $remainingDays = now()->diffInDays($policy->end_date);

        if ($remainingDays <= 0 || $totalDays <= 0) {
            return 0.0;
        }

        $proRata = ($remainingDays / $totalDays) * (float) $policy->gross_premium;

        return round($proRata * self::SHORT_PERIOD_ADMIN_FACTOR, 2);
    }
}