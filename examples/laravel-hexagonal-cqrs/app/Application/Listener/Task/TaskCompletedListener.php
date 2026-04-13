<?php

declare(strict_types=1);

namespace App\Application\Listener\Task;

use App\Domain\Event\Task\TaskCompleted;
use Illuminate\Support\Facades\Log;
use SolidFrame\EventDriven\EventListener;

final class TaskCompletedListener implements EventListener
{
    public function __invoke(TaskCompleted $event): void
    {
        Log::info('Task completed', [
            'taskId' => $event->taskId,
            'projectId' => $event->projectId,
        ]);
    }
}
