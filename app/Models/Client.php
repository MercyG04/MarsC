<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Relations\HasMany;


#[Fillable([
    'first_name',
    'last_name',
    'other_names',
    'national_id',
    'kra_pin',
    'phone_number',
    'email',
    'date_of_birth',
    'occupation',
    'county',
    'physical_location',
    'gender',
    'driving_experience',
    'consent_given',
    'consent_given_at',
])]
              

class Client extends Model
{
    protected  function casts(): array 
    {
        return [
            'date_of_birth' => 'date',
            'consent_given' => 'boolean',
            'consent_give_at' => 'datetime',
            'driving_experience' => 'integer',

        ];
        
    }
            
    

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->other_names} {$this->last_name}");
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    public function vehicles(): HasMany
    {
    return $this->hasMany(Vehicle::class);
    
    }
     public function policies(): HasMany
    {
    return $this->hasMany(Policy::class);
    }

    protected static function booted(): void
    {    parent::boot();
       static::saving(function (Client $client) {
           if ($client->consent_given && ! $client->consent_given_at) {
               $client->consent_given_at = now();
           }
           if (! $client->consent_given) {
               $client->consent_given_at = null;
           }
       });

    
    }
}   
