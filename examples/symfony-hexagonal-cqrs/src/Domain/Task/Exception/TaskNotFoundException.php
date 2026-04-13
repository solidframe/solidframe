<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

final class TaskNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Task with id "%s" was not found.', $id));
    }
}
