<?php

namespace App\Services;

use App\Models\Policy;
use Illuminate\Support\Facades\DB;

class PolicyNumberGenerator
{
    /**
     * Generate the next sequential policy number for the current year.
     * Format: POL-YYYY-NNNNN  (e.g. POL-2026-00001)
     */
    public function next(): string
    {
        $year   = now()->year;
        $prefix = "POL-{$year}-";

        return DB::transaction(function () use ($prefix) {
            // Lock the latest policy for this year to prevent race conditions
            $latest = Policy::withTrashed()
                ->where('policy_number', 'like', "{$prefix}%")
                ->orderByDesc('policy_number')
                ->lockForUpdate()
                ->first();

            $nextSequence = $latest
                ? ((int) substr($latest->policy_number, -5)) + 1
                : 1;

            return $prefix . str_pad($nextSequence, 5, '0', STR_PAD_LEFT);
        });
    }
}