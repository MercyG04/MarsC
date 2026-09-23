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
    public function baseRate(): float
    {
        return match ($this) {
            self::Comprehensive       => 4.00,
            self::ThirdParty          => 0.75,
            self::ThirdPartyFireTheft => 2.25,
        };
    
     }
}        