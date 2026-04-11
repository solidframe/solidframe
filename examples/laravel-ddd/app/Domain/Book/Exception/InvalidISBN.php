<?php

declare(strict_types=1);

namespace App\Domain\Book\Exception;

use InvalidArgumentException;
use SolidFrame\Core\Exception\SolidFrameException;

final class InvalidISBN extends InvalidArgumentException implements SolidFrameException
{
    public static function malformed(string $value): self
    {
        return new self(sprintf('ISBN "%s" is not a valid 13-digit ISBN.', $value));
    }

    public static function invalidCheckDigit(string $value): self
    {
        return new self(sprintf('ISBN "%s" has an invalid check digit.', $value));
    }
}
