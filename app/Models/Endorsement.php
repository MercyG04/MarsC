<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


#[fillable([
        'policy_id', 'endorsement_number', 'endorsement_type',
        'changes', 'additional_premium', 'refund_premium',
        'effective_date', 'reason', 'created_by',
    ])]
class Endorsement extends Model
{
    use SoftDeletes;

    

    protected function casts(): array
    {
        return [
            'changes'            => 'array',
            'additional_premium' => 'decimal:2',
            'refund_premium'     => 'decimal:2',
            'effective_date'     => 'date',
            'endorsement_type'   => \App\Enums\EndorsementType::class,
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}