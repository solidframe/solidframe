<?php

declare(strict_types=1);

namespace App\Domain\Task;

enum TaskStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
