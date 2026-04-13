<?php

declare(strict_types=1);

namespace App\Application\Listener\Task;

use App\Domain\Event\Task\TaskCreated;
use Psr\Log\LoggerInterface;
use SolidFrame\EventDriven\EventListener;

final class TaskCreatedListener implements EventListener
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function __invoke(TaskCreated $event): void
    {
        $this->logger->info('Task created', [
            'taskId' => $event->taskId,
            'projectId' => $event->projectId,
            'title' => $event->title,
        ]);
    }
}
