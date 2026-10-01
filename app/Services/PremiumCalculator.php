<?php

namespace App\Services;

use App\Enums\AddOnRateType;
use App\Enums\PolicyType;
use App\Models\AddOn;
use App\Models\Vehicle;

class PremiumCalculator
{
    /**
     * Statutory levy rates (as decimals, not percentages).
     */
    const TRAINING_LEVY_RATE = 0.002;   
    const PHCF_RATE          = 0.0025;  
    const STAMP_DUTY         = 40.00;   

    /**
     * Deductible calculation constants.
     */
    const DEDUCTIBLE_RATE = 0.025;      // 2.5% of ACV
    const DEDUCTIBLE_MIN  = 20000.00;   // KES 20,000
    const DEDUCTIBLE_MAX  = 100000.00;  // KES 100,000

    /**
     * Calculate the full premium breakdown for a vehicle.
     *
     * @param  Vehicle    $vehicle
     * @param  PolicyType $type
     * @param  array<int> $addOnIds  Array of AddOn IDs
     * @return array{
     *   basic_premium: float,
     *   training_levy: float,
     *   phcf: float,
     *   stamp_duty: float,
     *   add_ons: array<int, array{name: string, charged_amount: float}>,
     *   add_ons_total: float,
     *   gross_premium: float,
     *   sum_insured: float,
     *   deductible_amount: float
     * }
     */
    public function calculate(Vehicle $vehicle, PolicyType $type, array $addOnIds = []): array
    {
        $acv  = (float) $vehicle->currentValue();
        $use  = $vehicle->vehicle_use;

        // 1. Basic premium per pricing model
        $basicPremium = match ($type->pricingModel()) {
            'rate_based'  => $this->rateBasedPremium($acv, $type),
            'fixed_floor' => $this->fixedFloorPremium($type),
            'hybrid'      => $this->hybridPremium($acv, $type),
        };

        // 2. Apply floor if applicable
        if ($type->minimumPremium() !== null && $basicPremium < $type->minimumPremium()) {
            $basicPremium = $type->minimumPremium();
        }

        // 3. Apply vehicle use risk multiplier
        $basicPremium = round($basicPremium * $use->riskMultiplier(), 2);

        // 4. Statutory levies (calculated from the risk-loaded basic premium)
        $trainingLevy = round($basicPremium * self::TRAINING_LEVY_RATE, 2);
        $phcf         = round($basicPremium * self::PHCF_RATE, 2);
        $stampDuty    = self::STAMP_DUTY;

        // 5. Add-ons (no risk multiplier)
        $addOnsBreakdown = $this->calculateAddOns($acv, $addOnIds);
        $addOnsTotal     = round(array_sum(array_column($addOnsBreakdown, 'charged_amount')), 2);

        // 6. Gross premium
        $grossPremium = round(
            $basicPremium + $trainingLevy + $phcf + $stampDuty + $addOnsTotal,
            2
        );

        // 7. Deductible (stored on policy, not part of premium)
        $deductible = $this->calculateDeductible($acv);

        return [
            'basic_premium'     => $basicPremium,
            'training_levy'     => $trainingLevy,
            'phcf'              => $phcf,
            'stamp_duty'        => $stampDuty,
            'add_ons'           => $addOnsBreakdown,
            'add_ons_total'     => $addOnsTotal,
            'gross_premium'     => $grossPremium,
            'sum_insured'       => $acv,
            'deductible_amount' => $deductible,
        ];
    }

    /* ─────────────────────────────────────────────────────
     *  PRICING MODELS
     * ───────────────────────────────────────────────────── */

    protected function rateBasedPremium(float $acv, PolicyType $type): float
    {
        return round($acv * ($type->baseRate() / 100), 2);
    }

    protected function fixedFloorPremium(PolicyType $type): float
    {
        return (float) $type->minimumPremium();
    }

    protected function hybridPremium(float $acv, PolicyType $type): float
    {
        // TPO floor + percentage of ACV for fire/theft component
        $tpoFloor      = (float) PolicyType::ThirdParty->minimumPremium();
        $riskComponent = round($acv * ($type->baseRate() / 100), 2);

        return $tpoFloor + $riskComponent;
    }

    /* ─────────────────────────────────────────────────────
     *  ADD-ONS
     * ───────────────────────────────────────────────────── */

    /**
     * Calculate the charged amount for each selected add-on.
     * Percentage add-ons are calculated on sum insured (ACV).
     */
    protected function calculateAddOns(float $sumInsured, array $addOnIds): array
    {
        if (empty($addOnIds)) {
            return [];
        }

        $addOns = AddOn::whereIn('id', $addOnIds)
            ->where('is_active', true)
            ->get();

        return $addOns->map(function (AddOn $addOn) use ($sumInsured) {
            $charged = $addOn->rate_type === AddOnRateType::Fixed
                ? (float) $addOn->rate_value
                : round($sumInsured * ($addOn->rate_value / 100), 2);

            return [
                'id'             => $addOn->id,
                'name'           => $addOn->name,
                'code'           => $addOn->code,
                'charged_amount' => $charged,
            ];
        })->all();
    }

    /* ─────────────────────────────────────────────────────
     *  DEDUCTIBLE
     * ───────────────────────────────────────────────────── */

    /**
     * Own-damage excess: 2.5% of ACV, floored at 20,000, capped at 100,000.
     */
    protected function calculateDeductible(float $acv): float
    {
        $calculated = $acv * self::DEDUCTIBLE_RATE;

        return round(
            max(self::DEDUCTIBLE_MIN, min($calculated, self::DEDUCTIBLE_MAX)),
            2
        );
    }
}