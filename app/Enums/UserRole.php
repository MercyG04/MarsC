<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin         = 'admin';
    case Agent         = 'agent';
    case Underwriter   = 'underwriter';
    case ClaimsOfficer = 'claims_officer';
    public function label(): string
    {
        return match ($this) {
            self::Admin         => 'Administrator',
            self::Agent         => 'Insurance Agent',
            self::Underwriter   => 'Underwriter',
            self::ClaimsOfficer => 'Claims Officer',
        };
    }
}
