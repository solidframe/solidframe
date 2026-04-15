<?php

declare(strict_types=1);

namespace App\Domain\Account;

enum Currency: string
{
    case TRY = 'TRY';
    case USD = 'USD';
    case EUR = 'EUR';
}
