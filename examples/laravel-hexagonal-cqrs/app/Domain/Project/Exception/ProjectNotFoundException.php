<?php

declare(strict_types=1);

namespace App\Domain\Project\Exception;

use RuntimeException;
use SolidFrame\Core\Exception\SolidFrameException;

final class ProjectNotFoundException extends RuntimeException implements SolidFrameException
{
    public static function forId(string $id): self
    {
        return new self(sprintf('Project with id "%s" was not found.', $id));
    }
}
