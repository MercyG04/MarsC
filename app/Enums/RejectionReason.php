<?php

namespace App\Enums;

enum RejectionReason: string
{
    case PolicyInactive     = 'policy_inactive';         
    case FraudSuspected     = 'fraud_suspected';         
     case FraudProven        = 'fraud_proven';            
     case UnauthorizedDriver = 'unauthorized_driver';    
    case Illegality         = 'illegality';              
    case ScopeMismatch      = 'scope_mismatch';          
    case ExclusionApplied   = 'exclusion_applied';       
    case DocumentsIncomplete = 'documents_incomplete';   
    case Other              = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PolicyInactive      => 'Policy was not active at the time of loss',
            self::FraudSuspected      => 'Fraud suspected — evidence pending',
            self::FraudProven         => 'Fraud proven — claim denied',
            self::UnauthorizedDriver  => 'Driver not authorized on the policy',
            self::Illegality          => 'Illegal use at the time of the incident',
            self::ScopeMismatch       => 'Incident outside the policy cover',
            self::ExclusionApplied    => 'Damage falls under a policy exclusion',
            self::DocumentsIncomplete => 'Required documents not provided',
            self::Other               => 'Other (see notes)',
        };
    }

    public function requiresEvidence(): bool
    {
        return in_array($this, [self::FraudSuspected, self::FraudProven], true);
    }
}