<?php

namespace App\Services;

use App\Enums\EndorsementType;
use App\Enums\PolicyStatus;
use App\Models\Endorsement;
use App\Models\Policy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EndorsementService
{
    public function __construct(
        protected PremiumCalculator $calculator,
    ) {}

    /**
     * Apply an endorsement to an active policy.
     *
     * @param  array  $changes  e.g. ['policy_type' => PolicyType::Comprehensive, 'add_on_ids' => [1,3]]
     */
    public function request(Policy $policy, array $changes, string $reason): Endorsement
    {
        if ($policy->status !== PolicyStatus::Active) {
            throw new \RuntimeException('Only active policies can be endorsed.');
        }

        return DB::transaction(function () use ($policy, $changes, $reason) {

            // 1. Compute what the NEW premium would be under the proposed changes
            $newBreakdown = $this->calculator->calculate(
                $policy->vehicle,
                $changes['policy_type'] ?? $policy->policy_type,
                $changes['add_on_ids'] ?? $policy->addOns->pluck('id')->toArray(),
            );

            // 2. Compute the pro-rata adjustment
            $adjustment = $this->calculateProRata(
                (float) $policy->gross_premium,
                (float) $newBreakdown['gross_premium'],
                $policy->start_date,
                $policy->end_date,
            );

            // 3. Create the endorsement record FIRST
            $endorsement = Endorsement::create([
                'policy_id'          => $policy->id,
                'endorsement_number' => $this->nextNumber(),
                'endorsement_type'   => $this->classifyChange($policy, $changes),
                'changes'            => $this->describeChanges($policy, $changes),
                'additional_premium' => $adjustment > 0 ? $adjustment : 0,
                'refund_premium'     => $adjustment < 0 ? abs($adjustment) : 0,
                'effective_date'     => now()->toDateString(),
                'reason'             => $reason,
                'created_by'         => Auth::id(),
            ]);

            // 4. Apply the changes to the policy itself
            $this->applyChanges($policy, $changes, $newBreakdown, $adjustment);

            return $endorsement;
        });
    }

    /**
     * Compute the pro-rata adjustment for the remaining days of the policy term.
     */
    protected function calculateProRata(
        float $oldPremium,
        float $newPremium,
        Carbon $startDate,
        Carbon $endDate,
    ): float {
        $totalDays     = $startDate->diffInDays($endDate);
        $remainingDays = now()->diffInDays($endDate);

        if ($totalDays <= 0 || $remainingDays <= 0) {
            return 0.0;
        }

        $ratio = $remainingDays / $totalDays;

        return round(($newPremium - $oldPremium) * $ratio, 2);
    }

    /**
     * Apply the new terms to the policy and adjust the premium balance.
     */
    protected function applyChanges(
        Policy $policy,
        array $changes,
        array $newBreakdown,
        float $adjustment,
    ): void {
        $policy->update([
            'policy_type'     => $changes['policy_type'] ?? $policy->policy_type,
            'basic_premium'   => $newBreakdown['basic_premium'],
            'training_levy'   => $newBreakdown['training_levy'],
            'phcf'            => $newBreakdown['phcf'],
            'stamp_duty'      => $newBreakdown['stamp_duty'],
            'gross_premium'   => $newBreakdown['gross_premium'],
            'sum_insured'     => $newBreakdown['sum_insured'],
            'deductible_amount' => $newBreakdown['deductible_amount'],
            'premium_balance' => round((float) $policy->premium_balance + $adjustment, 2),
        ]);

        // If add-ons changed, sync them
        if (array_key_exists('add_on_ids', $changes)) {
            $pivot = [];

            foreach ($newBreakdown['add_ons'] as $addOn) {
                $pivot[$addOn['id']] = ['charged_amount' => $addOn['charged_amount']];
            }

            $policy->addOns()->sync($pivot);
        }
    }

    /**
     * Describe the change as a JSON-friendly diff.
     */
    protected function describeChanges(Policy $policy, array $changes): array
    {
        $diff = [];

        if (isset($changes['policy_type']) && $changes['policy_type'] !== $policy->policy_type) {
            $diff['policy_type'] = [
                'from' => $policy->policy_type->value,
                'to'   => $changes['policy_type']->value,
            ];
        }

        if (array_key_exists('add_on_ids', $changes)) {
            $diff['add_ons'] = [
                'from' => $policy->addOns->pluck('id')->toArray(),
                'to'   => $changes['add_on_ids'],
            ];
        }

        return $diff;
    }

    /**
     * Classify the endorsement type from the changes.
     */
    protected function classifyChange(Policy $policy, array $changes): EndorsementType
    {
        $policyTypeChanged = isset($changes['policy_type'])
            && $changes['policy_type'] !== $policy->policy_type;

        $addOnsChanged = array_key_exists('add_on_ids', $changes);

        if ($policyTypeChanged && $addOnsChanged) {
            return EndorsementType::AddOnsChanged;
        }

        if ($policyTypeChanged) {
            return EndorsementType::PolicyTypeChange;
        }

        if ($addOnsChanged) {
            $old = $policy->addOns->pluck('id')->toArray();
            $new = $changes['add_on_ids'];

            if (count($new) > count($old)) {
                return EndorsementType::AddOnAdded;
            }

            if (count($new) < count($old)) {
                return EndorsementType::AddOnRemoved;
            }

            return EndorsementType::AddOnsChanged;
        }

        return EndorsementType::Correction;
    }

    /**
     * Generate the next endorsement number for the current year.
     * Format: END-YYYY-NNNNN
     */
    protected function nextNumber(): string
    {
        $prefix = 'END-' . now()->year . '-';

        $latest = Endorsement::where('endorsement_number', 'like', "{$prefix}%")
            ->orderByDesc('endorsement_number')
            ->lockForUpdate()
            ->first();

        $next = $latest
            ? ((int) substr($latest->endorsement_number, -5)) + 1
            : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}