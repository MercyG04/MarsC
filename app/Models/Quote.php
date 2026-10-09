<?php

namespace App\Models;

use App\Enums\PolicyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['quote_number','client_id',
                'prospect_name','prospect_phone',
                'prospect_email','vehicle_details',
                'policy_type','selected_add_on_ids',
                'premium_breakdown','gross_premium',
                'status','valid_until','sent_at',
                'accepted_at','created_by' ])]

class Quote extends Model
{ use SoftDeletes;
    protected function casts() :array
    {
        return [
            'vehicle_details' => 'array',
            'selected_add_on_ids'=> 'array',
            'premium_breakdown' => 'array',
            'status'    => QuotationStatus::class,
            'policy_type'=> PolicyType::class,
            'valid_until' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'gross_premium' => 'decimal:2',


        ];
    }
    public function client():BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
    public function createdBy():BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function policy():HasOne
    {
        return $this->hasOne(Policy::class);
    }
}
