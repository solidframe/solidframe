<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

class AggregateNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Aggregate with id "%s" was not found.', $id));
    }
}
