<?php

declare(strict_types=1);

namespace App\Application\Command\ReopenTask;

use SolidFrame\Cqrs\Command;

final readonly class ReopenTask implements Command
{
    public function __construct(public string $taskId)
    {
    }
}
