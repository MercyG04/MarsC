<?php

namespace App\Enums;

enum ClaimStatus: string
{
    case Submitted       = 'submitted';        // client has filed the claim
    case Acknowledged    = 'acknowledged';     // insurer has acknowledged (within 24h per AKI)
    case UnderReview     = 'under_review';     // documents being verified, assessor assigned
    case Assessed        = 'assessed';         // assessor's report received
    case Approved        = 'approved';         // insurer admits liability
    case Rejected        = 'rejected';         // insurer denies liability
    case Paid            = 'paid';             // payment has been made

    public function label(): string
    {
        return match ($this) {
            self::Submitted    => 'Submitted',
            self::Acknowledged => 'Acknowledged',
            self::UnderReview  => 'Under Review',
            self::Assessed     => 'Assessed',
            self::Approved     => 'Approved',
            self::Rejected     => 'Rejected',
            self::Paid         => 'Paid',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Rejected, self::Paid], true);
    }

    public function canTransitionTo(self $new): bool
    {
        return match ($this) {
            self::Submitted    => in_array($new, [self::Acknowledged, self::UnderReview, self::Rejected], true),
            self::Acknowledged => in_array($new, [self::UnderReview, self::Rejected], true),
            self::UnderReview  => in_array($new, [self::Assessed, self::Rejected], true),
            self::Assessed     => in_array($new, [self::Approved, self::Rejected], true),
            self::Approved     => $new === self::Paid,
            self::Rejected, self::Paid => false,
        };
    }
}