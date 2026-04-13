<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

use InvalidArgumentException;
use SolidFrame\Core\Exception\SolidFrameException;

final class InvalidTaskTitle extends InvalidArgumentException implements SolidFrameException
{
    public static function empty(): self
    {
        return new self('Task title cannot be empty.');
    }

    public static function tooLong(string $value): self
    {
        return new self(sprintf('Task title "%s..." exceeds 255 characters.', mb_substr($value, 0, 30)));
    }
}
