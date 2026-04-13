<?php

declare(strict_types=1);

namespace App\Application\Listener\Task;

use App\Domain\Event\Task\TaskCompleted;
use Psr\Log\LoggerInterface;
use SolidFrame\EventDriven\EventListener;

final class TaskCompletedListener implements EventListener
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function __invoke(TaskCompleted $event): void
    {
        $this->logger->info('Task completed', [
            'taskId' => $event->taskId,
            'projectId' => $event->projectId,
        ]);
    }
}
