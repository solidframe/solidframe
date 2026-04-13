<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

use InvalidArgumentException;
use SolidFrame\Core\Exception\SolidFrameException;

final class InvalidTaskDescription extends InvalidArgumentException implements SolidFrameException
{
    public static function tooLong(string $value): self
    {
        return new self(sprintf('Task description "%s..." exceeds 1000 characters.', mb_substr($value, 0, 30)));
    }
}
