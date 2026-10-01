<?php

namespace App\Enums;

enum EndorsementType: string
{
    case PolicyTypeChange  = 'policy_type_change';
    case AddOnAdded        = 'add_on_added';
    case AddOnRemoved      = 'add_on_removed';
    case AddOnsChanged     = 'add_ons_changed';
    case SumInsuredChange  = 'sum_insured_change';
    case DeductibleChange  = 'deductible_change';
    case DriverAdded       = 'driver_added';
    case DriverRemoved     = 'driver_removed';
    case Correction        = 'correction';
    case TermExtension     = 'term_extension';

    public function label(): string
    {
        return match ($this) {
            self::PolicyTypeChange => 'Cover Type Change',
            self::AddOnAdded       => 'Add-On Added',
            self::AddOnRemoved     => 'Add-On Removed',
            self::AddOnsChanged    => 'Add-Ons Changed',
            self::SumInsuredChange => 'Sum Insured Change',
            self::DeductibleChange => 'Deductible Change',
            self::DriverAdded      => 'Driver Added',
            self::DriverRemoved    => 'Driver Removed',
            self::Correction       => 'Correction',
            self::TermExtension    => 'Term Extension',
        };
    }

    /**
     * Does this endorsement typically require additional premium?
     * (Upgrade changes)
     */
    public function isUpgrade(): bool
    {
        return match ($this) {
            self::PolicyTypeChange,
            self::AddOnAdded,
            self::AddOnsChanged,
            self::SumInsuredChange,
            self::DriverAdded,
            self::TermExtension    => true,

            self::AddOnRemoved,
            self::DriverRemoved,
            self::DeductibleChange,
            self::Correction       => false,
        };
    }

    /**
     * Can this endorsement result in a refund?
     * (Downgrade changes)
     */
    public function canGenerateRefund(): bool
    {
        return match ($this) {
            self::PolicyTypeChange,
            self::AddOnRemoved,
            self::AddOnsChanged,
            self::DeductibleChange,
            self::DriverRemoved    => true,

            self::AddOnAdded,
            self::SumInsuredChange,
            self::DriverAdded,
            self::Correction,
            self::TermExtension    => false,
        };
    }
}