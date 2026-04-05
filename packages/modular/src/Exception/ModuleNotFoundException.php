<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

class ModuleNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forName(string $name): self
    {
        return new self(sprintf('Module "%s" was not found.', $name));
    }
}
