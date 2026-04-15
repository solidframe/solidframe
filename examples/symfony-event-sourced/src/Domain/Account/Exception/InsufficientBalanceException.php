<?php

declare(strict_types=1);

namespace App\Domain\Account\Exception;

use DomainException;
use SolidFrame\Core\Exception\SolidFrameException;

final class InsufficientBalanceException extends DomainException implements SolidFrameException
{
    public static function forWithdrawal(string $accountId, int $requested, int $available): self
    {
        return new self(sprintf(
            'Account "%s" has insufficient balance: requested %d, available %d.',
            $accountId,
            $requested,
            $available,
        ));
    }

    public static function forTransfer(string $accountId, int $requested, int $available): self
    {
        return new self(sprintf(
            'Account "%s" has insufficient balance for transfer: requested %d, available %d.',
            $accountId,
            $requested,
            $available,
        ));
    }
}
