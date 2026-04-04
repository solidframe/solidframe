<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

class HandlerNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forMessage(object $message): self
    {
        return new self(sprintf('No handler found for message of type "%s".', $message::class));
    }
}
