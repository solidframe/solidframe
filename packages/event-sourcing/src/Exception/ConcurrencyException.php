<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

class ConcurrencyException extends RuntimeException implements SolidFrameException
{
    public static function forAggregate(string $aggregateId, int $expectedVersion, int $actualVersion): self
    {
        return new self(sprintf(
            'Concurrency conflict for aggregate "%s": expected version %d, actual version %d.',
            $aggregateId,
            $expectedVersion,
            $actualVersion,
        ));
    }
}
