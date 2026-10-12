<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use App\Enums\ClaimType;
use App\Enums\RejectionReason;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'claim_number',
    'policy_id',
    'incident_date',
    'description',
    'police_abstract_ref',
    'location',
    'driver_id',
    'claimed_amount',
    'assessed_amount',
    'approved_amount',
    'deductible_applied',
    'salvage_value',
    'claim_type',
    'adjustment_type',
    'status',
    'rejection_reason',
    'rejection_notes',
    'assigned_to',
    'assessor_id',
    'reported_at',
    'acknowledged_at',
    'assessed_at',
    'approved_at',
    'rejected_at',
    'paid_at',
    'discharge_voucher_signed_at',
    'created_by',
])]
class Claim extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'incident_date'               => 'immutable_date',
            'claimed_amount'              => 'decimal:2',
            'rejection_reason'           =>  RejectionReason::class,
            'assessed_amount'             => 'decimal:2',
            'approved_amount'             => 'decimal:2',
            'deductible_applied'          => 'decimal:2',
            'salvage_value'               => 'decimal:2',
            'claim_type'                  => ClaimType::class,
            'status'                      => ClaimStatus::class,
            'reported_at'                 => 'datetime',
            'acknowledged_at'             => 'datetime',
            'assessed_at'                 => 'datetime',
            'approved_at'                 => 'datetime',
            'rejected_at'                 => 'datetime',
            'paid_at'                     => 'datetime',
            'discharge_voucher_signed_at' => 'datetime',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
    

    public function driver(): BelongsTo
    {
        return $this->belongsTo(VehicleDriver::class, 'driver_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClaimDocument::class);
    }

    /* ---------- Helpers ---------- */

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function netPayout(): ?float
    {
        if (! $this->approved_amount) {
            return null;
        }

        return round(
            (float) $this->approved_amount
            - (float) ($this->deductible_applied ?? 0)
            - (float) ($this->salvage_value ?? 0),
            2
        );
    }
}