<?php

declare(strict_types=1);

namespace App\Application\Listener\Task;

use App\Domain\Event\Task\TaskAssigned;
use Psr\Log\LoggerInterface;
use SolidFrame\EventDriven\EventListener;

final class TaskAssignedListener implements EventListener
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function __invoke(TaskAssigned $event): void
    {
        $this->logger->info('Task assigned', [
            'taskId' => $event->taskId,
            'assignee' => $event->assignee,
        ]);
    }
}
