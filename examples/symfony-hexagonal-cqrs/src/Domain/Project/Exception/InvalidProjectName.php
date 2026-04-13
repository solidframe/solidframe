<?php

declare(strict_types=1);

namespace App\Domain\Project\Exception;

use InvalidArgumentException;
use SolidFrame\Core\Exception\SolidFrameException;

final class InvalidProjectName extends InvalidArgumentException implements SolidFrameException
{
    public static function empty(): self
    {
        return new self('Project name cannot be empty.');
    }

    public static function tooLong(string $value): self
    {
        return new self(sprintf('Project name "%s..." exceeds 100 characters.', mb_substr($value, 0, 30)));
    }
}
