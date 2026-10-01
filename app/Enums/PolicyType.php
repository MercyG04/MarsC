<?php

namespace App\Enums;

enum PolicyType: string
{
    case Comprehensive         = 'comprehensive';
    case ThirdParty            = 'third_party';
    case ThirdPartyFireTheft   = 'third_party_fire_theft';

    public function label(): string
    {
        return match ($this) {
            self::Comprehensive       => 'Comprehensive',
            self::ThirdParty          => 'Third Party Only',
            self::ThirdPartyFireTheft => 'Third Party Fire & Theft',
        };
    }

    public function pricingModel(): string
    {
        return match ($this) {
            self::Comprehensive       => 'rate_based',
            self::ThirdParty          => 'fixed_floor',
            self::ThirdPartyFireTheft => 'hybrid',
        };
    }
    public function baseRate(): float
    {
        return match ($this) {
            self::Comprehensive       => 3.50,
            self::ThirdParty          => 0.00,
            self::ThirdPartyFireTheft => 1.00,
        };
    
     }
     public function minimumPremium(): ?float
    {
        return match ($this) {
            self::ThirdParty          => 5000.00,   // statutory floor
            self::ThirdPartyFireTheft => 5000.00,  // TPO + fire/theft loading
            self::Comprehensive       => null,      // no floor
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Comprehensive       => 'Full cover for own damage, third-party liability, fire and theft.',
            self::ThirdParty          => 'Covers third-party liability only. Statutory minimum cover.',
            self::ThirdPartyFireTheft => 'Third-party liability plus fire and theft of your own vehicle.',
        };
    }
}        