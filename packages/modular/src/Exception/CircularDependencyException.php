<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

class CircularDependencyException extends RuntimeException implements SolidFrameException
{
    public static function forModules(string ...$moduleNames): self
    {
        return new self(sprintf(
            'Circular dependency detected between modules: %s.',
            implode(' -> ', $moduleNames),
        ));
    }
}
