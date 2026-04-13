<?php

declare(strict_types=1);

namespace App\Application\Command\CreateTask;

use SolidFrame\Cqrs\Command;

final readonly class CreateTask implements Command
{
    public function __construct(
        public string $taskId,
        public string $projectId,
        public string $title,
        public ?string $description = null,
        public string $priority = 'medium',
    ) {
    }
}
