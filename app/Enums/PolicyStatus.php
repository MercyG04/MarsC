<?php

namespace App\Enums;

enum PolicyStatus: string
{
    case Pending   = 'pending';
    case Active    = 'active';
    case Cancelled = 'cancelled';
    case Expired   = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Active    => 'Active',
            self::Cancelled => 'Cancelled',
            self::Expired   => 'Expired',
        };
    }
}