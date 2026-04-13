<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

final class TaskAlreadyCompletedException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Task with id "%s" is already completed.', $id));
    }
}
