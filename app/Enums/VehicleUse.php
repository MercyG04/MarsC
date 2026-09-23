<?php

namespace App\Enums;

enum VehicleUse: string
{
    case Commercial         = 'commercial';
    case PSV         = 'psv';
    case Personal   = 'personal';
       public function label(): string
    {
        return match ($this) {
            self::Commercial         => 'Commercial',
            self::PSV        => 'Public Service Vehicle',
            self::Personal   => 'Personal',
            
        };
    }

    public function riskMultiplier(): float
    {
        return match ($this) {
            self::Personal    => 1.0,
            self::Commercial => 1.4,
            self::PSV        => 1.8,
        };
    }
}
