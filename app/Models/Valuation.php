<?php

namespace App\Models;

use App\Enums\ValuationType;
use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['vehicle_id',
    'valuation_date',
    'valuation_firm',
    'valuer_name',
    'valuation_type',
    'actual_cash_value',
    'forced_sale_value',
    'report_path',
    'notes',
    'created_by',])]
class Valuation extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'valuation_date'     => 'date',
            'valuation_type'     => ValuationType::class,
            'actual_cash_value'  => 'decimal:2',
            'forced_sale_value'  => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDepreciationFromDeclaredAttribute(): ?float
    {
        $declared = (float) $this->vehicle->initial_estimated_value;
        $acv      = (float) $this->actual_cash_value;

        if ($declared <= 0) {
            return null;
        }

        return round((($declared - $acv) / $declared) * 100, 2);
    }

    protected static function booted(): void
{
    static::created(function (Valuation $valuation) {
        $vehicle = $valuation->vehicle;

        if ($vehicle && $vehicle->status === VehicleStatus::PendingValuation) {
            $vehicle->status = VehicleStatus::Valued;
            $vehicle->saveQuietly();
        }
    });
}

}
