<?php

declare(strict_types=1);

namespace App\Domain\Book\Exception;

use InvalidArgumentException;
use SolidFrame\Core\Exception\SolidFrameException;

final class InvalidAuthor extends InvalidArgumentException implements SolidFrameException
{
    public static function empty(): self
    {
        return new self('Author name cannot be empty.');
    }

    public static function tooLong(string $value): self
    {
        return new self(sprintf('Author name "%s..." exceeds 255 characters.', mb_substr($value, 0, 30)));
    }
}
