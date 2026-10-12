<?php

namespace App\Enums;

enum ClaimDocumentType: string
{
    case PoliceAbstract    = 'police_abstract';
    case Photos            = 'photos';
    case DriverLicence     = 'driver_licence';
    case Logbook           = 'logbook';
    case DriverStatement   = 'driver_statement';
    case ClaimForm         = 'claim_form';
    case AssessorReport    = 'assessor_report';
    case DischargeVoucher  = 'discharge_voucher';
    case Other             = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PoliceAbstract   => 'Police Abstract',
            self::Photos           => 'Accident Photos',
            self::DriverLicence    => 'Driver\'s Licence',
            self::Logbook          => 'Vehicle Logbook',
            self::DriverStatement  => 'Driver\'s Statement',
            self::ClaimForm        => 'Completed Claim Form',
            self::AssessorReport   => 'Assessor\'s Report',
            self::DischargeVoucher => 'Discharge Voucher',
            self::Other            => 'Other',
        };
    }

    /**
     * Documents required for the claim to progress to assessment.
     */
    public static function requiredForIntake(): array
    {
        return [
            self::ClaimForm,
            self::PoliceAbstract,
            self::DriverLicence,
            self::Logbook,
            self::DriverStatement,
        ];
    }
}