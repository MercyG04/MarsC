<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft    = 'draft';
    case Sent     = 'sent';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired  = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft    => 'Draft',
            self::Sent     => 'Sent',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Expired  => 'Expired',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::Accepted,
            self::Declined,
            self::Expired,
        ], true);
    }
}