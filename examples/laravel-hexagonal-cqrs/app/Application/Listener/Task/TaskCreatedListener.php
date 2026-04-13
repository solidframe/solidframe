<?php

declare(strict_types=1);

namespace App\Application\Listener\Task;

use App\Domain\Event\Task\TaskCreated;
use Illuminate\Support\Facades\Log;
use SolidFrame\EventDriven\EventListener;

final class TaskCreatedListener implements EventListener
{
    public function __invoke(TaskCreated $event): void
    {
        Log::info('Task created', [
            'taskId' => $event->taskId,
            'projectId' => $event->projectId,
            'title' => $event->title,
        ]);
    }
}
