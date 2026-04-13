<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProject;

use SolidFrame\Cqrs\Command;

final readonly class CreateProject implements Command
{
    public function __construct(
        public string $projectId,
        public string $name,
        public ?string $description = null,
    ) {
    }
}
