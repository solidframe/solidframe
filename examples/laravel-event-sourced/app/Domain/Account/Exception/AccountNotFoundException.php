<?php

declare(strict_types=1);

namespace App\Domain\Account\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

final class AccountNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Account with id "%s" was not found.', $id));
    }
}
