<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'vehicle_id',
    'name',
    'national_id',
    'dl_number',
    'dl_class',
    'date_of_birth',
    'driving_experience',
    'is_primary',
])]

class VehicleDriver extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth'      => 'immutable_date',
            'driving_experience' => 'integer',
            'is_primary'         => 'boolean',
        ];
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }
    public function isYoungDriver(): bool
    {
        return $this->age < 25;
    }
    /**
     
     * Required for PSV vehicles.
     */
    public function hasPsvLicence(): bool
    {
        return str_contains(strtoupper($this->dl_class), 'A');
    }

}
