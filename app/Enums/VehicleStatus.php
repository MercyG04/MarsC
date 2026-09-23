<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case PendingValuation = 'pending_valuation';
    case Valued           = 'valued';
    case Insured          = 'insured';
    case Retired          = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::PendingValuation => 'Pending Valuation',
            self::Valued           => 'Valued',
            self::Insured          => 'Insured',
            self::Retired          => 'Retired',
        };
    }

    public function canReceivePolicy(): bool
    {
        return $this === self::Valued;
    }
}