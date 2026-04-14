<?php

declare(strict_types=1);

namespace Modules\Payment\Domain;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Charged = 'charged';
    case Refunded = 'refunded';
    case Failed = 'failed';
}
