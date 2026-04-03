<?php

declare(strict_types=1);

namespace SolidFrame\Core\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements SolidFrameException
{
    public static function emptyIdentity(string $class): self
    {
        return new self(sprintf('Identity of type "%s" cannot be empty.', $class));
    }

    public static function invalidUuid(string $value): self
    {
        return new self(sprintf('Value "%s" is not a valid UUID.', $value));
    }
}
