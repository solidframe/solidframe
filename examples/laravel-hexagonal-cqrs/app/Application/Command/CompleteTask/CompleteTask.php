<?php

declare(strict_types=1);

namespace App\Application\Command\CompleteTask;

use SolidFrame\Cqrs\Command;

final readonly class CompleteTask implements Command
{
    public function __construct(public string $taskId)
    {
    }
}
