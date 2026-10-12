<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'claim_id',
    'type',
    'original_filename',
    'path',
    'mime_type',
    'size_bytes',
    'uploaded_by',
    'received_at',
    'verified_at',
    'verified_by',
    'notes',
])]
class ClaimDocument extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'verified_at' => 'datetime',
            'size_bytes'  => 'integer',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}