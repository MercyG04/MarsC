<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\AddOn;
use App\Models\User;
use App\Enums\PolicyCancellationStatus;
use App\Enums\PolicyType;
use App\Enums\PolicyStatus;
use App\Enums\PaymentFrequency;



#[Fillable(['policy_number',
        'customer_id',
        'vehicle_id',
        'policy_type',
        'status',
        'issued_at',
        'start_date',
        'end_date',
        'basic_premium',
        'training_levy',
        'phcf',
        'stamp_duty',
        'add_ons',
        'gross_premium',
        'sum_insured',
        'deductible_amount',
        'payment_frequency',
        'certificate_isssued_at',
        'premium_balance',
        'cancellation_status',
        'cancellation_requested_at',
        'cancellation_effective_at',
        'certificate_surrendered_at',
        'refund_amount',
        'cancellation_reason',
        'cancelled_by',])]
class Policy extends Model
{
    protected  function casts(): array 
       { return [
        'issued_at'             => 'date',
        'start_date'            => 'immutable_date',
        'end_date'              => 'immutable_date',
        'basic_premium'         => 'decimal:2',
        'training_levy'         => 'decimal:2',
        'phcf'                  => 'decimal:2',
        'stamp_duty'            => 'decimal:2',
        'add_ons'               => 'array',
        'gross_premium'         => 'decimal:2',
        'sum_insured'           => 'decimal:2',
        'deductible_amount'     => 'decimal:2',
        'premium_balance'       => 'decimal:2',
        'policy_type'           => PolicyType::class,
        'status'                => PolicyStatus::class,
        'payment_frequency'     => PaymentFrequency::class,
        'certificate_issued_at' => 'datetime',
        'cancellation_status'        => PolicyCancellationStatus::class,
        'cancellation_requested_at'  => 'datetime',
        'cancellation_effective_at'  => 'datetime',
        'certificate_surrendered_at' => 'datetime',
        'refund_amount'              => 'decimal:2',
    ];
   } 

   public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    //public function claims(): HasMany
    //{
        //return $this->hasMany(Claim::class);
    //}
    
    public function getIsActiveAttribute(): bool
    {
        return $this->status === PolicyStatus::Active
            && $this->end_date->isFuture();
    }

    public function getDaysToExpiryAttribute(): int
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->premium_balance <= 0;
    }
    public function cancelledBy(): BelongsTo
    {
    return $this->belongsTo(User::class, 'cancelled_by');
    }
    
    public function getTotalAddOnsValueAttribute(): float
    {
        if (! $this->add_ons) {
            return 0;
        }

        return array_sum($this->add_ons);
    }

    public function addOns(): BelongsToMany
    {
    return $this->belongsToMany(AddOn::class, 'policy_add_on')
                ->withPivot('charged_amount')
                ->withTimestamps();
    }
    public function endorsements(): HasMany
    {
    return $this->hasMany(Endorsement::class);
    }
    protected static function booted(): void
    {
        static::saving(function (Policy $policy) {
            // Normalize policy number
            $policy->policy_number = strtoupper(trim($policy->policy_number));

            // Stamp duty default to 40 if not set
            if (is_null($policy->stamp_duty)) {
                $policy->stamp_duty = 40.00;
            }

            // Ensure premium_balance never goes below zero unless explicitly allowed
            // (Negative is allowed for overpayment — we just log a warning if it's too negative)
            if ($policy->premium_balance < -($policy->gross_premium)) {
                logger()->warning('Unusually large negative premium balance detected', [
                    'policy_number' => $policy->policy_number,
                    'balance'       => $policy->premium_balance,
                ]);
            }
        });

        static::retrieved(function (Policy $policy) {
            if ($policy->status === PolicyStatus::Active
                && $policy->end_date->isPast()) {
                $policy->status = PolicyStatus::Expired;
                $policy->saveQuietly();
            }
        });
    }
}
