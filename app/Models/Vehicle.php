<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Valuation;

use App\Enums\VehicleUse;
use App\Enums\VehicleStatus;


#[Fillable (['client_id',
        'registration_number',
        'chassis_number',
        'logbook_number',
        'make',
        'model',
        'year_of_manufacture',
        'initial_estimated_value',
        'vehicle_use',
        'status',
        'ntsa_verified',
        'ntsa_verified_at',])]
class Vehicle extends Model
{
    protected  function casts(): array 
    {
        return [
            
        'year_of_manufacture'      => 'integer',
        'initial_estimated_value'  => 'decimal:2',
        'vehicle_use'              => VehicleUse::class,
        'status'                    =>VehicleStatus::class,
        'ntsa_verified'            => 'boolean',
        'ntsa_verified_at'         => 'datetime',
    

        ];
        
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function policies(): HasMany
    {
    return $this->hasMany(Policy::class);
    }
    
    public function valuations(): HasMany
    {
    return $this->hasMany(Valuation::class);
    
    }
    public function latestValuation(): ?Valuation
    {
    return $this->valuations()
                ->orderByDesc('valuation_date')
                ->first();
    }
    public function currentValue(): float
    {
    return (float) ($this->latestValuation()?->actual_cash_value
        ?? $this->initial_estimated_value);
    }
    

    public function getDisplayNameAttribute(): string
    {
        return "{$this->year_of_manufacture} {$this->make} {$this->model} ({$this->registration_number})";
    }

    public function getAgeAttribute(): int
    {
        return now()->year - $this->year_of_manufacture;
    }

    protected static function booted(): void
    {
        // Normalize registration number casing: "kda 123a" → "KDA 123A"
        static::saving(function (Vehicle $vehicle) {
        $vehicle->registration_number = strtoupper(trim($vehicle->registration_number));
        $vehicle->chassis_number      = strtoupper(trim($vehicle->chassis_number));
        $vehicle->logbook_number      = strtoupper(trim($vehicle->logbook_number));
        });


        static::updating(function (Vehicle $vehicle) {
    $protected = [
        'registration_number',
        'chassis_number',
        'logbook_number',
        'initial_estimated_value',
    ];

    foreach ($protected as $field) {
        if ($vehicle->isDirty($field) && $vehicle->exists) {
            throw new \RuntimeException("Field '{$field}' is immutable on a vehicle.");
        }
    }
    });
    }

    


}
