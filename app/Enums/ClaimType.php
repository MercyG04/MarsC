<?php

namespace App\Enums;

enum ClaimType: string
{
    case Accident   = 'accident';
    case Theft      = 'theft';
    case Fire       = 'fire';
    case ThirdParty = 'third_party';

    public function label(): string
    {
        return match ($this) {
            self::Accident   => 'Accident',
            self::Theft      => 'Theft',
            self::Fire       => 'Fire',
            self::ThirdParty => 'Third Party Claim',
        };
    }
}