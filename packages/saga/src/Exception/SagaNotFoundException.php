<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

class SagaNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Saga with id "%s" was not found.', $id));
    }
}
