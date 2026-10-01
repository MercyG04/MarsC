<?php

namespace App\Enums;

enum PolicyCancellationStatus: string
{
    case Pending              = 'pending';               
    case CertificatePending   = 'certificate_pending';   
    case Completed            = 'completed';             
    case Rejected             = 'rejected';              

    public function label(): string
    {
        return match ($this) {
            self::Pending            => 'Cancellation Requested',
            self::CertificatePending => 'Awaiting Certificate Surrender',
            self::Completed          => 'Cancelled',
            self::Rejected           => 'Cancellation Rejected',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Rejected], true);
    }
}