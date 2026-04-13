<?php

declare(strict_types=1);

namespace App\Application\Command\AssignTask;

use SolidFrame\Cqrs\Command;

final readonly class AssignTask implements Command
{
    public function __construct(
        public string $taskId,
        public string $assignee,
    ) {
    }
}
