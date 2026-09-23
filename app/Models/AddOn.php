<?php

namespace App\Models;

use App\Enums\AddOnRateType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Policy;


#[Fillable(['name','code','rate_type','rate_value','description','is_active'])]

class AddOn extends Model
   
{   use SoftDeletes;
    protected function casts(): array
    {
    return [
        'rate_value' => 'decimal:2',
        'is_active'  => 'boolean',
        'rate_type'  => AddOnRateType::class,
    ];
    }
    


    public function policies(): BelongsToMany
    {
    return $this->belongsToMany(Policy::class, 'policy_add_on')
                ->withPivot('charged_amount')
                ->withTimestamps();
    }
}
