<?php
namespace App\Enums;

enum ValuationType: string
{
    case Initial      = 'initial';
    case Periodic     = 'periodic';
    case PostIncident = 'post_incident';
    case Disputed     = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Initial      => 'Initial Valuation',
            self::Periodic     => 'Periodic / Renewal Valuation',
            self::PostIncident => 'Post-Incident Assessment',
            self::Disputed     => 'Disputed / Second Opinion',
        };
    }        
}    