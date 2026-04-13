<?php

declare(strict_types=1);

namespace App\Application\Listener\Task;

use App\Domain\Event\Task\TaskAssigned;
use Illuminate\Support\Facades\Log;
use SolidFrame\EventDriven\EventListener;

final class TaskAssignedListener implements EventListener
{
    public function __invoke(TaskAssigned $event): void
    {
        Log::info('Task assigned', [
            'taskId' => $event->taskId,
            'assignee' => $event->assignee,
        ]);
    }
}
