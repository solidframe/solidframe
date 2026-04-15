<?php

declare(strict_types=1);

namespace App\Domain\Account\Exception;

use DomainException;
use SolidFrame\Core\Exception\SolidFrameException;

final class InvalidAmountException extends DomainException implements SolidFrameException
{
    public static function notPositive(int $amount): self
    {
        return new self(sprintf('Amount must be positive, got %d.', $amount));
    }
}
