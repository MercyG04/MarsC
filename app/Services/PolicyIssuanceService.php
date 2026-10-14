<?php

namespace App\Services;

use App\Enums\PaymentFrequency;
use App\Enums\PolicyStatus;
use App\Enums\PolicyType;
use App\Enums\VehicleStatus;
use App\Models\Policy;
use App\Models\Vehicle;
use App\Events\PolicyIssued;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PolicyIssuanceService
{
    public function __construct(
        protected PremiumCalculator       $calculator,
        protected PolicyNumberGenerator   $numberGenerator,
    ) {}

    /**
     * Issue a policy for a vehicle.
     */
    
    public function issue(Vehicle $vehicle, array $data): Policy
{
    $policy = DB::transaction(function () use ($vehicle, $data) {

        // 1. Calculate the full premium breakdown
        $type = PolicyType::from($data['policy_type']);
        $breakdown = $this->calculator->calculate(
            $vehicle,
            $type,
            $data['add_on_ids'] ?? [],
        );

        // 2. Generate a unique policy number
        $policyNumber = $this->numberGenerator->next();

        // 3. Compute dates
        $startDate = $data['start_date'] ?? now()->toDateString();
        $endDate   = \Carbon\Carbon::parse($startDate)->addYear()->subDay()->toDateString();

        // 4. Determine payment balance
        $grossPremium = $breakdown['gross_premium'];
        $balance = $grossPremium;

        // 5. Create the policy
        $policy = Policy::create([
            'policy_number'         => $policyNumber,
            'client_id'             => $vehicle->client_id,
            'vehicle_id'            => $vehicle->id,
            'policy_type'           => $type,
            'status'                => PolicyStatus::Active,
            'issued_at'             => now()->toDateString(),
            'start_date'            => $startDate,
            'end_date'              => $endDate,
            'basic_premium'         => $breakdown['basic_premium'],
            'training_levy'         => $breakdown['training_levy'],
            'phcf'                  => $breakdown['phcf'],
            'stamp_duty'            => $breakdown['stamp_duty'],
            'gross_premium'         => $grossPremium,
            'sum_insured'           => $breakdown['sum_insured'],
            'deductible_amount'     => $breakdown['deductible_amount'],
            'payment_frequency'     => $data['payment_frequency'],
            'premium_balance'       => $balance,
        ]);

        // 6. Attach add-ons
        foreach ($breakdown['add_ons'] as $addOn) {
            $policy->addOns()->attach($addOn['id'], [
                'charged_amount' => $addOn['charged_amount'],
            ]);
        }

        // 7. Flip vehicle status
        $vehicle->update(['status' => VehicleStatus::Insured]);

        return $policy->fresh(['addOns']);
    });

    // 8. Fire the event AFTER commit
    event(new PolicyIssued($policy));

    return $policy;
    }
}