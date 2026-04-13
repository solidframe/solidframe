<?php

declare(strict_types=1);

namespace App\Application\Command\CompleteTask;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\TaskId;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class CompleteTaskHandler implements CommandHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(CompleteTask $command): void
    {
        $task = $this->tasks->find(new TaskId($command->taskId))
            ?? throw TaskNotFoundException::forId($command->taskId);

        $task->complete();

        $this->tasks->save($task);

        foreach ($task->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
