<?php

namespace App\Enums;

enum AddOnRateType: string
{
    case Percentage = 'percentage';
    case Fixed      = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage of Sum Insured',
            self::Fixed      => 'Fixed Amount',
        };
    }
}