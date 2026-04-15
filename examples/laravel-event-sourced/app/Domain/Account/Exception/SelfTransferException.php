<?php

declare(strict_types=1);

namespace App\Domain\Account\Exception;

use DomainException;
use SolidFrame\Core\Exception\SolidFrameException;

final class SelfTransferException extends DomainException implements SolidFrameException
{
    public static function forAccount(string $accountId): self
    {
        return new self(sprintf('Cannot transfer to the same account "%s".', $accountId));
    }
}
