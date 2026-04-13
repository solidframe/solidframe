<?php

declare(strict_types=1);

namespace App\Application\Command\ArchiveProject;

use SolidFrame\Cqrs\Command;

final readonly class ArchiveProject implements Command
{
    public function __construct(public string $projectId)
    {
    }
}
