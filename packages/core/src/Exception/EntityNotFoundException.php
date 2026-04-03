<?php

declare(strict_types=1);

namespace SolidFrame\Core\Exception;

use RuntimeException;

class EntityNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Entity with id "%s" was not found.', $id));
    }

    public static function forClassAndId(string $class, string $id): self
    {
        return new self(sprintf('Entity of type "%s" with id "%s" was not found.', $class, $id));
    }
}
