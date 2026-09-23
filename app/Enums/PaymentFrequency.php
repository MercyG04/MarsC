<?php

namespace App\Enums;

enum PaymentFrequency: string
{
    case Annual  = 'annual';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::Annual  => 'Annual (One-Time)',
            self::Monthly => 'Monthly Installments',
        };
    }
     public function months(): int
    {
        return match ($this) {
            self::Annual  => 12,
            self::Monthly => 1,
        };
    }
}